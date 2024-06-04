// Create the tooltips only when document ready
$(document).ready(function() {
    // MAKE SURE YOUR SELECTOR MATCHES SOMETHING IN YOUR HTML!!!
    $('.redre-help-pop a').each(function() {
        $(this).click(function(event) {
            event.preventDefault();
        });
        $(this).qtip({
            content: {
                text: function(event, api) {
                    $.ajax({
                            url: api.elements.target.attr('href') // Use href attribute as URL
                        })
                        .then(function(content) {
                            // Set the tooltip content upon successful retrieval
                            api.set('content.text', content);
                        }, function(xhr, status, error) {
                            // Upon failure... set the tooltip content to error
                            api.set('content.text', status + ': ' + error);
                        });

                    return 'Loading...'; // Set some initial text
                }
            },
            position: {
                viewport: $(window)
            },
            hide: {
                fixed: true,
                delay: 300
            },
            style: 'qtip-wiki'
        });
    });

    $(document.body).on('click', '.fancybox', function() {
        $.fancybox({
            href: this.href,
            type: $(this).data("type")
        }); // fancybox
        return false
    }); // on
    
    $("#add_product").click(function(e) {
        if ($('#crd').val() != "0") 
            window.location.href = $('#crd').val();    
    });

});

var spanSorting = '<span class="arrow-hack sort">&nbsp;&nbsp;&nbsp;</span>';
var spanAsc = '<span class="arrow-hack asc">&nbsp;&nbsp;&nbsp;</span>';
var spanDesc = '<span class="arrow-hack desc">&nbsp;&nbsp;&nbsp;</span>';

function fb_block(message) {
    $.blockUI({ message: '<div class="fb_block"><span class="fb_message">' + message + '</span><img src="' + base_url + 'assets/images/ajax-loader-horizontal.gif" /></div>',
                overlayCSS:  { 
                    backgroundColor: '#888', 
                    opacity:         0.6, 
                    cursor:          'wait' 
                }, 
                css: {
                    border: '4px solid green', 
                    padding: "20px", 
                    borderRadius: "8px", 
                    background: "#fff", 
                    width: "320px",
                    top: "50%",
                    left: "50%",
                    marginTop: "-50px",
                    marginLeft: "-160px" }
    });
    
}

function fb_unblock() {
    $.unblockUI();
}

function fb_block_selector(selector) {
    jQuery(selector).block({ message: ' \
        <div class="fb_block"><div class="next-spinner"> \
            <svg class="next-icon next-icon--size-40"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#next-spinner"></use></svg> \
        </div></div> \
        <div id="global-icon-symbols" data-tg-refresh="global-icon-symbols" data-tg-refresh-always="true" style="display: none;"><svg xmlns="http://www.w3.org/2000/svg"><symbol id="next-spinner" class="icon-symbol--loaded"><svg preserveAspectRatio="xMinYMin"><circle class="next-spinner__ring" cx="50%" cy="50%" r="45%"></circle></svg></symbol></svg></div>',
                            overlayCSS: { backgroundColor: '#fff', opacity: '0.6', zIndex: 99999 },
                            css: {border: 'none', background: "transparent", width: "32px", height: "32px", top: "40%", left: "50%", marginLeft: "-16px", zIndex: 99999 } });
}

function fb_unblock_selector(selector) {
    jQuery(selector).unblock({fadeOut:  0});
}

jQuery(document).ready(function($) {
    $(document).on("change", "#check_all_products", function() {
        if ($(this).is(":checked")) 
            $(".product_checkbox").prop("checked", true);
        else 
            $(".product_checkbox").prop("checked", false);
    });
    
    $("#fetch_tags").on('click',function() {
        fb_block("Please don\'t reload this page");
        $.ajax({
            url: base_url + "Track/fetch_tcode/",
            dataType: "json",                
            success: function( data ) {
                //var result = JSON.parse(data);
                if (data["status"] == 1) {
                    fb_unblock();
                    alert("Number of tags fetched: " + data["result"]["count"]);
                    if (data["result"]["count"] > 0)
                        location.reload();        
                }
            }
        });        
    });
    
    $("#fb_table").on('click', 'th', function() {
        $("#fb_table thead th").each(function(i, th) {
            $(th).find('.arrow-hack').remove();
            var html = $(th).html(),
                cls = $(th).attr('class');
            switch (cls) {
                case 'sorting_asc' : 
                    $(th).html(html+spanAsc); break;
                case 'sorting_desc' : 
                    $(th).html(html+spanDesc); break;
                default : 
                    $(th).html(html+spanSorting); break;
            }
        });     
    });

    $("#fb_table thead th").each(function(i, th) {
        $(th).find('.arrow-hack').remove();
        var html = $(th).html();                            
        $(th).html(html+spanSorting);
    });

    $(".bootstrap-switch").bootstrapSwitch();

    $("body").on("click", ".actions > .fb_delete", function(e) {
        if (!confirm("Are you sure you want to delete this row?")) {
            e.preventDefault();
            return;        
        }
    });

    $(".select2").select2();


});        