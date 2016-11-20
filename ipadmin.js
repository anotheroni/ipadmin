
function showDomainUsers(form)
{
    var myindex=form.domain.selectedIndex;
    var address="?a=admin_users&domain="+(form.domain.options[myindex].value);
    top.location.href=address;
}

function showDomainCourses(form)
{
    var myindex=form.domain.selectedIndex;
    var address="?a=edit_courses&domain="+(form.domain.options[myindex].value);
    top.location.href=address;
}

function checkHouseConnection(form, reson)
{
    var fromV=form.from.selectedIndex;
    var toV=form.to.selectedIndex;
    if(form.from.options[fromV].value == form.to.options[toV].value) {
        alert(reson);
        return false;
    }
    else {
        form.submit();
        return true;
    }
}

function confirmOk(message)
{
    var agree=confirm(message);
    if (agree)
        return true ;
    else
        return false ;
}

// Used by admin users
function confirmSubmit(form, message)
{
	var agree = confirm(message)
	if(agree) {
		form.submit();
		return true;
	} else {
		return false;
	}
}

function editProfile(form, message1, message2, message3)
{
    if(form.n_oldpswd.value.length != 0) { 
        if(form.n_newpswd1.value.length == 0 || form.n_newpswd2.value.length == 0) {
            alert(message2);
            return false;
        } 

        if(form.n_newpswd1.value != form.n_newpswd2.value) {
            alert(message1);
            return false;
        }
    }
    
    if(form.n_firstname.value.length == 0 || form.n_surname.value.length == 0) {
        alert(message3);
        return false;
    }

    form.submit();
	 return true;
}

function filterBookings(form)
{   
    form.submit();
}

function checkHouseAddNew(form, message)
{
    if(form.name.value.length == 0)
    {
        alert(message);
    }
    else
    {
        form.submit();
    }
}

function checkUser(form, message)
{
	if(form.n_username.value.length == 0 || form.n_firstname.value.length == 0
		|| form.n_username.value.length == 0)
	{
		alert(message);
	}
	else
	{
		form.submit();
	}
}

function checkCourse(form, message)
{
	if(form.n_coursename.value.length == 0 || 
		form.n_coursecode.value.length == 0 ||
		form.n_year.value.length == 0 ||
		form.n_students.value.length == 0)
	{
		alert(message);
	}
	else
	{
		form.submit();
	}
}

function newDate(form, year, mon, day)
{
	form.action = form.action + "&year=" + year +"&mon=" + mon + "&day=" + day
						+ "&bt=calender";
	form.submit();
	return false;
}

function newDatePeriodic(form, year, mon, day)
{
	form.action = form.action + "&year=" + year +"&mon=" + mon + "&day=" + day
						+ "&bt=calender";
	form.submit();
	return false;
}

function newDatePeriodicEnd(form, year, mon, day)
{
	form.action = form.action + "&yearEnd=" + year +"&monEnd=" + mon +
					  	"&dayEnd=" + day +
						"&bt=calender";
	form.submit();
	return false;
}

function newDateView(form, year, mon, day)
{
	form.action = form.action + "&year=" + year +"&mon=" + mon + "&day=" + day;
	form.submit();
	return false;
}

function newDateViewEnd(form, year, mon, day)
{
	form.action = form.action + "&yearEnd=" + year +"&monEnd=" + mon +
					  	"&dayEnd=" + day;
	form.submit();
	return false;
}

function searchView(form, sYear, eYear, sMon, eMon, sDay, eDay, msg)
{
	if (sYear > eYear || (sMon > eMon && sYear==eYear) ||
		(sDay > eDay && sMon == eMon && sYear==eYear))
	{
		alert(msg);
	}else{
		form.action = form.action + "2";
		form.submit();
	}	
}

// Used by book room
function checkTime(form, message)
{
	if(form.starttime.selectedIndex > form.endtime.selectedIndex)
	{
		alert(message);
		return false;
	}
	else
	{
		form.submit();
		return true;
	}
}

// Used by periodic_booking
function checkAddBooking(form, sYear, eYear, sMon, eMon, sDay, eDay,
	tYear, tMon, tDay, numBookings, dateMsg, timeMsg, courseMsg, startMsg)
{
	if(numBookings < 1)
	{
		if(form.course.selectedIndex == 0)
		{
			alert(courseMsg);
			return;
		}
	}

	if(form.starttime.selectedIndex > form.endtime.selectedIndex)
	{
		alert(timeMsg);
		return;
	}

	if(sYear > eYear || (sMon > eMon && sYear == eYear) || 
		(sDay > eDay && sMon == eMon && sYear == eYear)) {
		alert(dateMsg);
		return;
	}

	if(sYear < tYear || (sYear == tYear && sMon < tMon) ||
		 (sYear == tYear && sMon == tMon && sDay < tDay)) {
		alert(startMsg);
		return;
	}

	form.action = form.action + "&bt1a=Add";

	form.submit();	
}

// Used by periodic_booking
function checkBookings(form, num_bookings, message, waitmessage)
{
	if(num_bookings == 0)
	{
		alert(message);
	}
	else
	{
		form.action = form.action + "&bt1n=Next";
		alert(waitmessage);
		form.submit();
	}
}

// Used by show course and show room
function pulldownEvent(form)
{
	form.submit();
}

function show_room(roomnr, housenr)
{
    local = window.open("index.php?a=room_info&shouse=" + housenr + "&sroom=" + roomnr + "", "local","height=500,width=450,resizable=yes,scrollbars=yes,toolbar=no,menubar=no")
}
