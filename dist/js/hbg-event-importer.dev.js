var ImportEvents = ImportEvents || {};
ImportEvents.loading = false;
ImportEvents.data = {'action' : 'import_events', 'value': ''};
ImportEvents.short = 200;
ImportEvents.long = 400;
ImportEvents.timerId = null;

jQuery(document).ready(function ($) {
    $('#cbis, #xcap').click(function() {
        if(!ImportEvents.loadingOccasions)
        {
            ImportEvents.loadingOccasions = true;
            var button = $(this);
            var storedCss = collectCssFromButton(button);
            redLoadingButton(button, function() {
                ImportEvents.data.value = button.attr('id');
                jQuery.post(ajaxurl, ImportEvents.data, function(response) {
                    var newPosts = response;
                    console.log(newPosts);
                    ImportEvents.loadingOccasions = false;
                    $('#blackOverlay').show();
                    var responsePopup = $('#importResponse');
                    responsePopup.show(500, function() {
                        var eventNumber = responsePopup.find('#event');
                        var locationNumber = responsePopup.find('#location');
                        var contactNumber = responsePopup.find('#contact');
                        var normalTextSize = eventNumber.css('fontSize');
                        var bigTextSize = '26px'
                        eventNumber.text(newPosts.events);
                        locationNumber.text(newPosts.locations);
                        contactNumber.text(newPosts.contacts);
                        eventNumber.animate({opacity: 1}, ImportEvents.long).animate({fontSize: bigTextSize}, ImportEvents.short).animate({fontSize: normalTextSize}, ImportEvents.short, function() {
                            locationNumber.animate({opacity: 1}, ImportEvents.long).animate({fontSize: bigTextSize}, ImportEvents.short).animate({fontSize: normalTextSize}, ImportEvents.short, function() {
                                contactNumber.animate({opacity: 1}, ImportEvents.long).animate({fontSize: bigTextSize}, ImportEvents.short).animate({fontSize: normalTextSize}, ImportEvents.short, function() {
                                    var loadingBar = responsePopup.find('#untilReload #meter');
                                    loadingBar.animate({width: '100%'}, 10000, function() {
                                        location.reload();
                                    });
                                });
                            });
                        });
                    });
                    restoreButton(button, storedCss);
                });
            });
        }
    });

    $('#occasions').click(function() {
        if(!ImportEvents.loadingOccasions)
        {
            ImportEvents.loadingOccasions = true;
            var button = $(this);
            var storedCss = collectCssFromButton(button);
            redLoadingButton(button, function() {
                var data = {
                    'action'    : 'collect_occasions'
                };

                jQuery.post(ajaxurl, data, function(response) {
                    console.log(response);
                    ImportEvents.loadingOccasions = false;
                    restoreButton(button, storedCss);
                });
            });
        }
    });

    $('.notice.is-dismissible').on('click', '.notice-dismiss', function(event){
        dismissInstructions();
    });

    $('.accept').click(function() {
        var postId = $(this).attr('postid');
        changeAccepted(1, postId);
    });

    $('.deny').click(function() {
        var postId = $(this).attr('postid');
        changeAccepted(-1, postId);
    });

    $('.acf-gallery-add').text("Add images");

    var oldInput = '';
    $('input[name="post_title"]').on('change paste keyup', function() {
        var input = $(this).val();

        if(input == oldInput)
            return;

        oldInput = input;
        if(input.length > 3)
        {
            var data = {
                'action'    : 'check_existing_title',
                'value'     : input,
                'postType'  : pagenow
            };
            var isevent = (pagenow === 'event') ? true : false;
            var geturl = (isevent) ? '/json/wp/v2/' + pagenow + '/search?term=' + input : '/json/wp/v2/' + pagenow + '?search=' + input;
            //jQuery.get('/json/wp/v2/' + pagenow + '?search=' + input, function(response) {
            jQuery.get(geturl, function(response) {
                $('#suggestionList').empty();
                for(var i in response) {
                    var id = response[i].id;
                    var title = (isevent) ? response[i].title : response[i].title.rendered;
                    //console.log('Id: ' + id + ', Title: ' + title);
                    // $('#suggestionList').append('<li><a href="/wp/wp-admin/post.php?post=' + id + '&action=edit&lightbox=true" class="suggestion">' + title + '</a></li>');
                    $('#suggestionList').append('<li><a href="/wp/wp-admin/post.php?post=' + id + '&action=edit" class="suggestion">' + title + '</a></li>');
                }
                if($('.suggestion').length == 0)
                    $('#suggestionContainer').hide();
                else
                {
                    $('#suggestionContainer').show();
                    // $('.suggestion').click(function(event) {
                    //     event.preventDefault();
                    //     ImportEvents.Prompt.Modal.open($(this).attr('href'));
                    // });
                }
            });
        }
        else
            $('#suggestionContainer').hide();
    });

    if(pagenow == 'contact' || pagenow == 'location' || pagenow == 'event' || pagenow == 'sponsor' || pagenow == 'package')
    {
        $('#titlewrap').after('<div id="suggestionContainer"><ul id="suggestionList"></ul></div>');
    }
    if(pagenow == 'edit-event')
    {
        $('#wpwrap').append('<div id="blackOverlay"></div>');
        $('.wrap').append('\
            <div id="importResponse">\
                <div><p>New data imported</p></div>\
                <div class="inline"><p>Events</p></div><div class="inline"><p>Locations</p></div><div class="inline"><p>Contacts</p></div>\
                <div class="inline"><p id="event">0</p></div><div class="inline"><p id="location">0</p></div><div class="inline"><p id="contact">0</p></div>\
                <div id="untilReload"><div id="meter"></div><p>Time until reload</p></div>\
            </div>\
        ');
    }
});

function collectCssFromButton(button)
{
    return {
        bgColor: button.css('background-color'),
        textColor: button.css('color'),
        borderColor: button.css('border-color'),
        textShadow: button.css('text-shadow'),
        boxShadow: button.css('box-shadow'),
        width: button.css('width'),
        text: button.text()
    };
}

function redLoadingButton(button, callback)
{
    button.fadeOut(500, function() {
        var texts = ['Loading&nbsp;&nbsp;&nbsp;', 'Loading.&nbsp;&nbsp;', 'Loading..&nbsp;', 'Loading...'];
        button.css('background-color', 'rgb(255, 210, 77)');
        button.css('border-color', 'rgb(255, 191, 0)');
        button.css('color', 'white');
        button.css('text-shadow', '0 -1px 1px rgb(230, 172, 0),1px 0 1px rgb(230, 172, 0),0 1px 1px rgb(230, 172, 0),-1px 0 1px rgb(230, 172, 0)');
        button.css('box-shadow', 'none');
        button.css('width', '85px');
        button.html(texts[0]);
        button.fadeIn(500);

        var counter = 1;
        ImportEvents.timerId = setInterval(function()
        {
            if(counter > 3)
                counter = 0;
            button.html(texts[counter]);
            ++counter;
        }, 500);
        if(callback != undefined)
            callback();
    });
}

function restoreButton(button, storedCss)
{
    button.fadeOut(500, function() {
        button.css('background-color', storedCss.bgColor);
        button.css('color', storedCss.textColor);
        button.css('border-color', storedCss.borderColor);
        button.css('text-shadow', storedCss.textShadow);
        button.css('box-shadow', storedCss.boxShadow);
        button.css('width', storedCss.width);
        button.text(storedCss.text);
        button.fadeIn(500);
        clearTimeout(ImportEvents.timerId);
    });
}

/**
 * Creates data with values for ajax, and also runs the ajax
 * @param  int newValue either -1,0,1
 * @param  int postId   wordpress post id
 * @return void
 */
function changeAccepted(newValue, postId) {
    var data = {
        'action'    : 'my_action',
        'value'     : newValue,
        'postId'    : postId
    };

    var postElement = jQuery('#post-' + postId);
    toggleClasses(postElement, newValue);
    jQuery.post(ajaxurl, data, function(response) {
        console.log(response);
    });
}

/**
 * Changing the background of a event post
 * @param  jQuery object, base event element
 * @param  int responseValue
 * @return void
 */
function toggleClasses(element, responseValue) {
    if(responseValue == 1) {
        element.removeClass('red');
        element.addClass('green');
        element.find('.accept').addClass('hiddenElement');
        element.find('.deny').removeClass('hiddenElement');
    } else if(responseValue == -1) {
        element.removeClass('green');
        element.addClass('red');
        element.find('.accept').removeClass('hiddenElement');
        element.find('.deny').addClass('hiddenElement');
    }
}

/**
 * Hides event instructions if clicked.
 * @return void
 */
function dismissInstructions() {
    var data = {
        'action'    : 'dismiss'
    };
    
    jQuery.post(ajaxurl, data);
}


jQuery(document).ready(function ($) {

    $('.acf-field[data-name="sync"] input[type="checkbox"]').on('change', function () {
        if ($('.acf-field[data-name="sync"] input[type="checkbox"]').is(':checked')) {
            $('body').addClass('no-sync');
            $(this).parent().addClass('check_active');
        } else {
            $('body').removeClass('no-sync');
            $(this).parent().removeClass('check_active');
        }
    }).trigger('change');

    $('.acf-field[data-name="main_organizer"] input[type="checkbox"]').each(function(i, obj) {
        if ($(this).prop('checked')) {
            $('.acf-field[data-name="main_organizer"]').not(this.closest('.acf-field[data-name="main_organizer"]')).addClass('main_organizer_hidden');
        }
    });

    $('.acf-field[data-name="main_organizer"] input[type="checkbox"]').live('click', function () {
        if ($(this).prop('checked')) {
            $('.acf-field[data-name="main_organizer"]').not(this.closest('.acf-field[data-name="main_organizer"]')).addClass('main_organizer_hidden');
        } else {
            $('.acf-field[data-name="main_organizer"]').removeClass('main_organizer_hidden');
        }
    });
});

var ImportEvents = ImportEvents || {};

jQuery(document).ready(function ($) {
    if($('.acf-field-57ebb807988f8').length)
    {
        //add this class for a button instead of link 'page-title-action'
        $('.acf-field-57ebb807988f8').append('<a class="createContact button" href="http://' + window.location.host + '/wp/wp-admin/post-new.php?post_type=contact&lightbox=true">Create new contact</a>');
    }

    if($('.acf-field-57a9d5f3804e1').length)
    {
        //add this class for a button instead of link 'page-title-action'
        $('.acf-field-57a9d5f3804e1').append('<a class="createContact button" href="http://' + window.location.host + '/wp/wp-admin/post-new.php?post_type=sponsor&lightbox=true">Create new sponsor</a>');
    }

    if($('.acf-field-576117c423a52').length)
    {
        //add this class for a button instead of link 'page-title-action'
        $('.acf-field-576117c423a52').append('<a class="createContact button" href="http://' + window.location.host + '/wp/wp-admin/post-new.php?post_type=location&lightbox=true">Create new location</a>');
    }

    if($('.acf-field-57c7ed92054e6').length)
    {
        //add this class for a button instead of link 'page-title-action'
        $('.acf-field-57c7ed92054e6').append('<a class="createContact button" href="http://' + window.location.host + '/wp/wp-admin/post-new.php?post_type=membership-card&lightbox=true">Create new membership card</a>');
    }

    $('.openContact').click(function(event) {
        event.preventDefault();
        ImportEvents.Prompt.Modal.open($(this).attr('href'));
    });

    $('.createContact').click(function(event) {
        var parentId = $('#post_ID').val();
        event.preventDefault();
        ImportEvents.Prompt.Modal.open($(this).attr('href'), parentId);
    });
});
ImportEvents = ImportEvents || {};
ImportEvents.Prompt = ImportEvents.Prompt || {};

ImportEvents.Prompt.Modal = (function ($) {

    //console.log('First');
    var isOpen = false;

    function Modal() {
        //console.log('new Modal!');
        $(function() {
            this.handleEvents();
        }.bind(this));
    }

    Modal.prototype.open = function (url, parentId) {
        //console.log('Open iframe');
        $('body').addClass('lightbox-open').append('\
            <div id="lightbox">\
                <div class="lightbox-wrapper">\
                    <button class="lightbox-close" data-lightbox-action="close">&times; Close</button>\
                    <iframe class="lightbox-iframe" src="' + url + '" frameborder="0" allowtransparency></iframe>\
                </div>\
            </div>\
        ');

        if(typeof(parentId) != 'undefined')
        {
            //console.log('Parent id set');
            //console.log(parentId);
            $(".lightbox-iframe").bind("load",function() {
                var newContactForm = $(this).contents().find('#post');
                newContactForm.append('<input type="hidden" id="parentId" name="parentId" value="' + parentId + '" />');
            });
        }

        isOpen = true;
    };

    Modal.prototype.close = function () {
        //console.log('Close');
        var modalElement = $('.lightbox-iframe');
        //console.log(modalElement.find('#post_ID').val());
        //console.log(modalElement.contents().find('#post').find('#post_ID').val());
        $('body').removeClass('lightbox-open');
        $('#lightbox').remove();
        isOpen = false;
    };

    Modal.prototype.handleEvents = function () {
        //console.log('Handle events');
        $(document).on('click', '[data-lightbox-action="close"]', function (e) {
            e.preventDefault();
            this.close();
        }.bind(this));
    };

    return new Modal();

})(jQuery);
