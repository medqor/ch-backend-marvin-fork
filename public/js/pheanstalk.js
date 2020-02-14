let tubeData = $(document.currentScript).data('data');
if ($('#tube').length > 0) {
    tubeData.tube = $('#tube').val();
}

let tube = tubeData.tube;

let progressbars = [];
let emptyCalls = 0;
let maxCallsBeforeHangup = 120;


function pheanstalk() {
    let payload = {};
    payload.tube = tube
    $.getJSON('//queue.chroniclehealth.com/read.php', payload, function (data) {
        processResults(data.messages);
    })
}

pheanstalk();


function processResults(messages) {

    if (messages.length > 0) {
        $(messages).each(function (idx, data) {
            if (data.type != undefined) {
                switch (data.type) {
                    case 'append':
                        appendElement(data);
                        emptyCalls = 0;
                        break;
                    case 'html':
                        htmlElement(data);
                        emptyCalls = 0;
                        break;
                    case 'progressbar':
                        progressbarElement(data);
                        emptyCalls = 0;
                        break;
                    case 'ajax':
                        ajaxEvent(data);
                        emptyCalls = 0;
                        break;
                    case 'terminate':
                        emptyCalls = maxCallsBeforeHangup + 1;
                        $('body').append('<div class="ui-style-highlight">' + data.message + '</div>');
                        break;
                    case 'console':
                    default:
                        if (data.message == 'No jobs') {
                            emptyCalls++;
                        }
                        console.log(data);
                        break;
                }
            }
        })
    }

    if (emptyCalls <= maxCallsBeforeHangup) {
        setTimeout(pheanstalk, 500);
    }

}

$(document).ready(function () {
    $.ajax({
        method: "POST",
        url: '/hubspot_etl/process',
        data: {tube: tubeData.tube},
        dataType: "json"
    });
})


function ajaxEvent(data) {
    $.ajax({
        method: "POST",
        url: data.url,
        data: data.data,
        dataType: "json"
    })
        .done(function (msg) {
            processResults(msg);
        });
}

function htmlElement(data) {
    let target = '#' + data.target;
    if ($(target).length == 0) {
        $('body').append($("<div/>").attr('id', data.target));
    }
    $(target).html(data.message);
}

function appendElement(data) {
    let target = '#' + data.target;
    if ($(target).length == 0) {
        $('body').append($("<div/>").attr('id', data.target));
    }
    $(target).append(data.message + "<br/>");
}

function progressbarElement(data) {
    if (data.value == undefined) {
        data.value = 0;
    }
    let target = '#' + data.target;
    if ($(target).length == 0) {
        $('body').append($("<div/>").attr('id', data.target));

    }
    if (!progressbars[data.target]) {
        $(target).progressbar({value: data.value});
        progressbars[data.target] = true;
    } else {
        $(target).progressbar('option', 'value', data.value)
    }
}
