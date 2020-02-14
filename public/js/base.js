var addLoader = function(elem){
    var loader=$("<div class='ajaxloader'></div>");
        $(elem).append(loader);
}
var removeLoader = function(elem){
    $(elem).find('.ajaxloader').remove();
}

function exportTableToCSV($table, filename) {

    csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent($('.export').data('csv'));
    $(this)
        .attr({
            'download': filename,
            'href': csvData,
            'target': '_blank'
        }).promise().done(function(){

        })


}

// This must be a hyperlink
$(".export").on('click', function (event) {
    // CSV
    //exportTableToCSV.apply(this, [$('#dvData>table'), 'export.csv']);
    csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent($(this).data('csv'));
    $(this)
        .attr({
            'download': filename,
            'href': csvData,
            'target': '_blank'
        }).promise().done(function(){

        })


    // IF CSV, don't do event.preventDefault() or return false
    // We actually need this to be a typical hyperlink
});


var pollingInterval=10000;

$(document).ready(function(){
    $(document).delegate('body','click',function(){
        $('.error_message_drawer:visible').slideUp("fast");
    });
        $(document).delegate('#advertiser_id','click',function(){
            $(this).toggleClass('arrowDown, arrowUp').blur();
            $(this).find('span').blur();
            $('.navigation_dropdown').toggle();
        });
});
var flash = function(elements) {
    var opacity = 100;
    var color = "255, 255, 20" // has to be in this format since we use rgba
    var interval = setInterval(function() {
        opacity -= 3;
        if (opacity <= 0) clearInterval(interval);
        $(elements).css({background: "rgba("+color+", "+opacity/100+")"});
    }, 30)
};

function syntaxHighlight(json) {
    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
        var cls = 'number';
        if (/^"/.test(match)) {
            if (/:$/.test(match)) {
                cls = 'key';
            } else {
                cls = 'string';
            }
        } else if (/true|false/.test(match)) {
            cls = 'boolean';
        } else if (/null/.test(match)) {
            cls = 'null';
        }
        return '<span class="' + cls + '">' + match + '</span>';
    });
}

var showError = function(message){

    $('.error_message_drawer').html(message).slideDown("fast");
}


$(document).ready(function(){
    $("input[type='checkbox'],input[type='radio']")
        .parent()
        .prepend("<svg class='lcars-svg'><rect class='lcars-control'></rect></svg>");
    $(".lcars-select-label").click(function(){
        $(this).children("select").focus();
    });
    $(".lcars-accordion-heading")
        .append(" <span class='fa fa-caret-down'></span>")
        .click(function(){
            $(this).parent().children(".lcars-accordion-content").slideToggle(200);
        });
    $(".dropdown").click(function(){
        $(this).children(".lcars-dropdown").slideToggle(200).
        mouseleave(function(){
            $(this).slideUp(200);
        });
    })
    $("input[type='checkbox']")
        .change(function(){
            if($(this).prop("checked")) {
                $(this).parent().addClass("checked");
                $(this).parent().find("rect").css("fill","yellow");
            } else {
                $(this).parent().removeClass("checked");
                $(this).parent().find("rect").css("fill","black");
            }
        });
    $("input[type='radio']").change(function(){
        $(this).parent().parent().find("rect").css("fill","black");
        $(this).parent().find("rect").css("fill","yellow");
    });
});