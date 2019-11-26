/*jslint devel: true, browser: true, maxerr: 500, indent: 4 */

(function ($) {

    "use strict";

    var page;

    page = {};

    page.refreshHolidayGreetingList = function () {
        $.ajax({
            url: "/merry-sparkmas/messages/",
            success: function (data) {
                var greeting,
                    i,
                    numGreetings,
                    greetingElement,
                    holidayGreetingListElement,
                    twitterUrl,
                    dateString,
                    timeString,
                    numGreetingsLimit=13;

                holidayGreetingListElement = $('#holiday-greeting-list');

                holidayGreetingListElement.html("");

                numGreetings = data.length;

                numGreetingsLimit = Math.min(numGreetingsLimit, numGreetings);

                $("#num-holiday-greetings").html(numGreetings);

                for (i=0; i<numGreetingsLimit; i += 1) {
                    greeting = data[i];
                    dateString = $.format.date(greeting.dateCreated, "MMM d, yyyy");
                    timeString = $.format.date(greeting.dateCreated, "h:mmp");


                    greetingElement = $('<li/>');
                    greetingElement.append($('<span/>').addClass('dateCreated').html(dateString));
                    greetingElement.append($('<span/>').addClass('timeCreated').html(timeString));
                    greetingElement.append($('<span/>').addClass('holidayGreeting').html(greeting.holidayGreeting));
                    if (greeting.twitterUsername != '') {
                        twitterUrl = "http://twitter.com/" + greeting.twitterUsername;
                        greetingElement.append($('<a/>').addClass('twitterUsername').attr("href", twitterUrl).html("@" + greeting.twitterUsername));
                    }

                    holidayGreetingListElement.append(greetingElement);

                }
            }
        });
    };

    page.sendHolidayGreeting = function () {
        var holidayGreetingElement,
            holidayGreeting,
            twitterUsernameElement,
            twitterUsername;

        holidayGreetingElement = $("#holidayGreeting");
        holidayGreeting = holidayGreetingElement.val();
        twitterUsernameElement = $("#twitterUsername");
        twitterUsername = twitterUsernameElement.val();

        if (holidayGreeting.length == 0) {
            return;
        }

        //Clear the form.
        holidayGreetingElement.val("");
        twitterUsernameElement.val("");

        $.ajax({
            url: "/merry-sparkmas/messages/",
            type: "POST",
            data: {holidayGreeting: holidayGreeting, twitterUsername: twitterUsername},
            success: function () {
                page.refreshHolidayGreetingList();
            }
        });
    };

    page.updateHolidayGreetingLength = function () {
        $("#countHolidayGreeting").html($("#holidayGreeting").val().length);
    };

    $(document).ready(function () {

        $("#input-form").on("submit", function (event) {
            event.preventDefault();
            page.sendHolidayGreeting();
        });

        $("#image-gallery").magnificPopup({
            delegate: 'a', // child items selector, by clicking on it popup will open
            type: 'image'
            // other options
        });

        $("#holidayGreeting").on("change", function (event) {
            page.updateHolidayGreetingLength();
        });

        $("#holidayGreeting").on("keyup", function (event) {
            page.updateHolidayGreetingLength();
        });

        page.refreshHolidayGreetingList();

    }); // $(document).ready()

}(jQuery));
