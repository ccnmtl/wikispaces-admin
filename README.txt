The wikispaces-admin project contains the code for 2 applications that utilize the 
wikispaces SOAP api. 

== create_course_space.php ==
allows users with the appropriate affiliations to create new wikis.   
see docs/create_course_space.png for a screenshot 

== selfjoin_course.php ==
allows users with matching affliations to join a wiki that exists
see docs/selfjoin_course.png for a screenshot 

The applications also function as dashboards/portals for users of wikispaces, informing them
which wikis they have accounts on, and how to get to them.

This software works in conjunction with a Single Sign On system that
looks like Basic Auth to the application.  By the time users access this page, 
REMOTE_USER and USER_AFFILIATIONS will be set.

This work was created at CCNMTL (http://ccnmtl.columbia.edu)
