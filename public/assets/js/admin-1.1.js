$(function () {
    $('#side-menu').metisMenu();
});
window.opts = {};

$(function () {
    $('select[value]').each(function () {
        $(this).val(this.getAttribute("value"));
    });
});

//Loads the correct sidebar on window load,
//collapses the sidebar on window resize.
// Sets the min-height of #page-wrapper to window size
$(function () {
    $(window).bind("load resize", function () {
        topOffset = 50;
        width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
        if (width < 768) {
            $('div.navbar-collapse').addClass('collapse');
            topOffset = 100; // 2-row-menu
        } else {
            $('div.navbar-collapse').removeClass('collapse');
        }

        height = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1;
        height = height - topOffset;
        if (height < 1)
            height = 1;
        if (height > topOffset) {
            $("#page-wrapper").css("min-height", (height) + "px");
        }
    });

    var url = window.location;
    var element = $('ul.nav a').filter(function () {
        return this.href == url || url.href.indexOf(this.href) == 0;
    }).addClass('active').parent().parent().addClass('in').parent();
    if (element.is('li')) {
        element.addClass('active');
    }
});

$(document).ready(function () {

    //initialize beautiful datetime picker
    $("input[type=date]").datepicker();
    $("input[type=date]").datepicker("option", "dateFormat", "yy-mm-dd");
    var dateFormat = $("input[type=date]").datepicker( "option", "dateFormat" );
    $("input[type=date]").datepicker( "option", "dateFormat", "yy-mm-dd" );

    $('#created_stats').submit(function (e) {
        $('#stats').html('<p class="text-center"><i class="fa fa-spinner fa-spin fa-5x"></i></p>');
        e.preventDefault();
        var data = $(this).serializeArray();
        Request.get({
            action: "admin/dataAnalysis",
            data: data
        }, function (data) {
            $('#stats').html('');
            if (data.data) {
                Morris.Bar({
                    element: 'stats',
                    data: toArray(data.data),
                    xkey: 'y',
                    ykeys: ['a'],
                    labels: ['Total']
                });
            }
        });
    });

    $('#getstats').submit(function (e) {
        $('#stats').html('<p class="text-center"><i class="fa fa-spinner fa-spin fa-5x"></i></p>');
        e.preventDefault();
        var data = $(this).serializeArray();
        Request.get({
            action: "member/stats",
            data: data
        }, function (data) {
            $('#stats').html('');
            if (data.data) {
                Morris.Bar({
                    element: 'stats',
                    data: toArray(data.data),
                    xkey: 'y',
                    ykeys: ['a'],
                    labels: ['Total']
                });
            }
        });
    });

    $('button[name=message]').click(function (e) {
        var self = this;
        window.opts.subject = $(this).data("subject");
        window.opts.email = $(this).data("from");
        $('#message_modal').modal('show');
    });

    $('#messageform').submit(function (e) {
        e.preventDefault();
        var body = $('#body').val();
        Request.post({
            action: "employer/messages",
            data: {action: 'support', subject: window.opts.subject, email: window.opts.email, body: body}
        }, function (data) {
            $('#status').html('Message Sent Successfully!!!');
            $('#message_modal').modal('hide');
        });
    });

    // find all the selectors 
    var types = $('#addOptions select');
    types.on("change", function () { // bind the change function
        var value = $(this).val();

        // if text box is selected then show it and hide the file upload or vice-versa
        if (value === "text") {
            $("#type").find("input[type='text']").toggleClass("hide").attr("required", "");
            $("#type").find("input[type='file']").toggleClass("hide");
        } else if (value === "image") {
            $("#type").find("input[type='file']").toggleClass("hide");
            $("#type").find("input[type='text']").toggleClass("hide").removeAttr("required");
        }
    });

    $("#searchModel").change(function() {
        var self = $(this), target = $('#searchField');
        target.html('');
        Request.get({action: "admin/fields/" + self.val()}, function(data) {
            for (obj in data) {
                if (data[obj].name) {
                    target.append('<option value="' + data[obj].name + '">' + data[obj].name + '</option>');
                }
            }
        });
    });

    $(document).on('change', '#searchField', function(event) {
        var fields = ["created", "modified"],
            date = $.inArray(this.value, fields);
        if (date !== -1) {
            $("input[name=value]").val('');
            $("input[name=value]").datepicker();
            $("input[name=value]").datepicker("option", "dateFormat", "yy-mm-dd");
        };
    });
});

function toArray(object) {
    var array = $.map(object, function (value, index) {
        return [value];
    });
    return array;
}
