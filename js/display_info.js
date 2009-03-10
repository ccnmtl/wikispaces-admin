
function toggleInfo(target, course) {
  //log("entering toggleInfo");
 infoElem = getElement(course);
 
 //  when a users clicks 'info' a second time, the note goes away
 if (infoElem.style.display == "block") {
   hideElement(infoElem);
 } else {
 
   // first, toggle off any already open info notes
   infos = getElementsByTagAndClassName('div', 'courseInfo');
   forEach(infos, function(info) { info.style.display = "none" });

   showElement(infoElem);

 }
 return false;
}
