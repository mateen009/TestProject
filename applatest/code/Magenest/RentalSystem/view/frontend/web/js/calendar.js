require([
    'jquery',
    'moment',
    'rental_fullcalendar'
], function ($) {
    $(document).ready(function () {
        if ($("#userRents").length) {
            var event = JSON.parse($("#userRents").val());
            var eventColors = JSON.parse($("#rentColors").val());
            var today = new Date();
            var dd = String(today.getDate()).padStart(2, '0');
            var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
            var yyyy = today.getFullYear();

            today = yyyy + '-' + mm + '-' + dd;

            $('#calendar').fullCalendar({
                header: {
                    right: 'prev,next today',
                    left: 'title'
                },

                // customize the button names,
                // otherwise they'd all just say "list"
                views: {
                    listDay: {buttonText: 'list day'},
                    listWeek: {buttonText: 'list week'}
                },

                defaultView: 'month',
                nextDayThreshold: '01:00:00',
                defaultDate: today,
                displayEventTime: false,
                navLinks: true, // can click day/week names to navigate views
                editable: true,
                eventLimit: true, // allow "more" link when too many events
                eventAfterRender: function (event, element, view) {
                    var currentDate = new Date();
                    if (event.start < currentDate && event.end < currentDate) {
                        element.css('background-color', eventColors[0]);
                    } else if (event.start < currentDate && event.end > currentDate) {
                        element.css('background-color', eventColors[1]);
                    } else if (event.start > currentDate && event.end > currentDate) {
                        element.css('background-color', eventColors[2]);
                    }
                },
                events: event,

                eventClick: function (event) {
                    if (event.url) {
                        window.open(event.url);
                        return false;
                    }
                }
            });
        }
    });
});
