#!/usr/bin/python
"""
Add non-registed users to wikispaces private course wikis

batch_add_nonregistered.py [-s <space_name>  ] [-f <non-registered user file>][-h|--help]
"""


"""
We are reading from a courseworks generated file of non-registered users, and adding them to the wikispaces private wikis they belong to.

The file is in this format

dbeeby0,NRA,'Beeby, Daniel J.',ADMN_1010_001_2007_2
bwr2001,NRA,'Robbins, Bruce William',ADMN_1011_001_2007_2
ca2201,NRA,'Alonso, Carlos J',ADMN_1011_001_2007_2
cl2510,NRA,'Lomnitz, Claudio W',ADMN_1011_001_2007_2
"""

import sys
import getopt

import csv

from SOAPpy import WSDL
import SOAPpy


class Usage(Exception):
    def __init__(self, msg):
	self.msg = msg


URL = 'http://www.wikispaces.columbia.edu'
ADMIN_NAME = 'admin'
ADMIN_PASSWORD = open('./secret.txt', 'r').read().rstrip()
print ADMIN_PASSWORD
PROCESSED_USERS = 'already_added.pkl'
LOG_FILE = 'batch_add.log'
log = open(LOG_FILE, 'a')


def listUsers(space_name):
    siteApi = WSDL.Proxy(URL + '/site/api?wsdl')
    spaceApi = WSDL.Proxy(URL + '/space/api?wsdl')
    userApi = WSDL.Proxy(URL + '/user/api?wsdl')
    #pageApi = WSDL.Proxy(URL + '/page/api?wsdl')
    
    try:
	session = siteApi.login(ADMIN_NAME, ADMIN_PASSWORD)
	space = spaceApi.getSpace(session, space_name)
	print "Space Name is", space.name
	print "Space id is", space.id
	
	usernames = []
	members = spaceApi.listMembers(session, space.id)
	[usernames.append(member.username) for member in members]
	return usernames
	
    except SOAPpy.Types.faultType, e:
	print 'Invalid Login'
	print e

def addUsers(filename):
    from datetime import datetime 
    now  = datetime.now()
    print >> log, "## addUsers running at %s" % now.strftime('%m-%d-%Y %H:%M')
    siteApi = WSDL.Proxy(URL + '/site/api?wsdl')
    spaceApi = WSDL.Proxy(URL + '/space/api?wsdl')
    userApi = WSDL.Proxy(URL + '/user/api?wsdl')
    #pageApi = WSDL.Proxy(URL + '/page/api?wsdl')

    nra = csv.reader(open(filename, "rb"), quotechar="'")



    #
    # cache a file of already processed user+coursekey combiniations
    #
    already_processed = {}
    try: 
        cache = open(PROCESSED_USERS, 'rb')
        # already_processed = pickle.load(pkl)
        for uni_course in cache:
            uni_course = uni_course[:-1] # chop off the newline
            already_processed[uni_course] = 1
        cache.close()
        # print already_processed
    except:
        print >> log, "Couldn't open %s" % PROCESSED_USERS
        pass # ok if we fall through here, since adding again doesn't break anything

    
    try:
	session = siteApi.login(ADMIN_NAME, ADMIN_PASSWORD)
    except SOAPpy.Types.faultType, e:
	print >> log, 'Failed to create a wikispaces session'
	print >> log, e
        return


    for row in nra:
        uni = row[0]
        coursekey = row[3]
        uni_course = "%s-%s" % (uni, coursekey)
        if not (already_processed.has_key(uni_course)):
            # record that we have already processed this 
            already_processed[uni_course] = 1
            print >> log, "processing %s" % uni_course

            # check to see if the wikispace exists - skip to next new user if it doesn't
            space_name = coursekey2space(coursekey)
            # print >> log, "session: %s , space_name: %s" % (session, space_name) 
            # space = spaceApi.getSpace(session, space_name)
            try:
                space = spaceApi.getSpace(session, space_name)
            except SOAPpy.Types.faultType, e:
                print >> log, "%s does not exist. Continuing" % space_name
                continue
            
            # if it does, add the user to this space
            try:
                user = userApi.getUser(session, uni)
            except SOAPpy.Types.faultType, e:
                # create the user w/in wikispaces if they don't exist
                user = userApi.createUser(session, uni, 'nullp4ssw0rd', "%s@columbia.edu" % uni)

            added = spaceApi.addMember(session, space.id, user.id)

            if (added):
                print >> log, "Added %s to %s\n" % (uni, space_name)
            else:
                print >> log, "Failed to add %s to %s\n" % (uni, space_name)
                
            
        
    cache = open(PROCESSED_USERS, 'wb')
    # pickle.dump(already_processed, pkl)    
    for uni_course in already_processed.keys():
        print >> cache, "%s" % uni_course
    cache.close()

def coursekey2space(coursekey):
    """
    returns a wikispace friendly spacename
    """
    result = coursekey.replace('_', '-').lower()
    return result

def main(argv=None):
    # import pdb; pdb.set_trace()
    
    if argv == None:
        argv = sys.argv
    try:
        try:
            opts, args = getopt.getopt(argv[1:], "s:f:h", ["space=", "file=", "help"])
        except getopt.error, msg:
            raise Usage(msg)
        # more code, unchanged
    except Usage, err:
        print >>sys.stderr, err.msg
        print >>sys.stderr, "for help use --help"
        return 2

    if not opts:
        print __doc__
        return 2
    
    f = None
    arg = None
    for o, a in opts:
        if o == "-s" or o == "--space":
            f = listUsers
            arg = a
        elif o == '-f' or o == '--file':
            f = addUsers
            arg = a
        elif o == "-h" or o == "--help":
            print __doc__
            return 2
        else:            
            return 2
    results = f(arg)

    if not results:
        print "No Results Found"
    
    print results

if __name__ == "__main__":
    sys.exit(main())
                 
