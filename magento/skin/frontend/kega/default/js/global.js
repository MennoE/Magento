jQuery(document).ready(function(){
	Initialize.init();
	Dropdown.init();
	Register.init();
	Store.init();
});

/**
 * Initialize
 */
var Initialize = function($) {

	/**
	 * Adds js-enabled class to body element
	 */
	var init = function()
	{
		$("html").addClass('js-enabled');
		initEmptyFocus();

		if (jQuery('body').hasClass('catalog-category-view')) {
			$("img").lazyload();
		}
	};

	/**
	 * Empty input on focus when it has .empty-focus class
	 *
	 * clears content on inputs get focussed and have .empty-focus class. On
	 * blur the inital content is restored when no value is added.
	 */
	var initEmptyFocus = function()
	{
		jQuery(".empty-focus").each(function(){
			el = jQuery(this);
			el.attr('prefilled', el.val());
			el.focus(function(){
				if (jQuery(this).val() == jQuery(this).attr('prefilled')) {
					jQuery(this).val('');
				}
			});
			el.blur(function(){
				if (!jQuery(this).val()) {
					jQuery(this).val(jQuery(this).attr('prefilled'));
				}
			});
		});
	};

	return {
		init : function() { init(); }
	}
}(jQuery);

/**
 * Dropdown
 *
 */
var Dropdown = function() {

	var noLinks;
    var init = function()
    {
    	/**
    	 * Convert real select to ul based dropdown with the following line:
    	 */
    	//createFromSelect(jQuery(".size-select"), true);
    	
        var dropdowns = jQuery('.dropdown');
        if (!dropdowns.length) {
            return false;
        }

        // select size if only one size option available on product view page
        var sizeSelect = jQuery(".catalog-product-view .size-select");
        if(sizeSelect.size() > 0) {
	        var sizeOptions = sizeSelect.prev().find('ul li');
	        if(sizeOptions.size() == 1) {
	        	sizeOptions.addClass('active');
				jQuery(sizeSelect).val(
	        		sizeOptions.find('a').attr('rel')
	        	);
	        }
        }

        setEmptyValue(dropdowns);

        setActiveValue(dropdowns);

        dropdowns.click(function(e){
            return dropdownClick(e, jQuery(this));
        });
        dropdowns.mouseleave(function(e){
            return dropdownMouseleave(e, jQuery(this));
        });
    };

    /**
     * Dropdown.createFromSelect
     * Create custom dropdown from a regular <select>
     * Position original select outside of viewport afterwards
     *
     * @param select object
     * @return void
     */
    var createFromSelect = function(select, ignoreLinks)
    {
    	jQuery(select).each(function(){

    		var sel = jQuery(this);
    		var noLinksClass = '';

			if(isSizeSelect(select)){
				noLinks = false;
			}

    		if(ignoreLinks) {
    			noLinks = true;
    			noLinksClass = 'no-links ';
    		}
			if (select.hasClass('use-title')) {
				noLinksClass += 'use-title ';
			}

	        html = '<div class="dropdown ' + noLinksClass + 'dropdown-alt" rel="' + sel.attr('id') + '">';
	        html += '<ul>';

	        jQuery("option", sel).each(function(){
	        	var dropdowns = jQuery(this).parent();
	        	if (this.value) {
	                html += '<li' + (this.selected ? ' class="active"' : '') + '>';
	                if (isSizeSelect(dropdowns) || dropdowns.attr('id') == 'qty' || noLinks) {
	                	html += '<a href="#" rel="' + this.value + '">' + this.innerHTML + '</a>';
	                } else {
	                	html += '<a href="' + this.value + '">' + this.innerHTML + '</a>';
	                }
	                html += '</li>';
	            }
	        });

	        html += '</ul>';
	        html += '</div>';
	        jQuery(html).insertBefore(sel);

	        sel.addClass('structural');
    	});
    };

    /**
     * Dropdown.setActiveValue
     * Checks the dropdown for an active entry
     * and places the value in the label span
     *
     * @param dropdowns object
     * @return void
     */
    var setActiveValue = function(dropdowns)
    {
        var active;

        dropdowns.each(function(){
            active = jQuery(".active", this);
            if (active.length) {
                jQuery('span', this).html(jQuery("a", active).html());
            }
        });
    };

    /**
     * Create span for selected value (with -- as value)
     */
    var setEmptyValue = function(dropdowns)
    {
		var dropdown;

        dropdowns.each(function(){
			dropdown = jQuery(this);
			if (dropdown.hasClass('use-title')) {
				dropdown.prepend('<span class="empty">' + jQuery('#'+dropdown.attr('rel')).attr('title') + '</span>');
			} else {
				dropdown.prepend('<span class="empty">--</span>');
			}
		});
    };

    /**
     * Checks if dropdown should be converted to a link list
     * in case of size select on detail page
     *
     * @param dropdown Object
     */
    var isSizeSelect = function(dropdown)
    {
		return dropdown.hasClass('.size-select')
    };

    /**
     * Dropdown.dropdownClick
     * Changes selected value on click
     * Sends new value back to original select (if available)
     *
     * @param e
     * @param dropdown Object
     */
    var dropdownClick = function(e, dropdown)
    {
        var target = jQuery(e.target);
        if(e.target.tagName.toLowerCase() == 'a') {
            target = target.parents('li');
        }

        if((dropdown.hasClass('open') || isSizeSelect(dropdown)) && target.find('a').html()) {
            jQuery('span', dropdown).html(target.find('a').html());

            jQuery('.active', dropdown).removeClass('active');
            target.addClass('active');

            if (dropdown.attr('rel')) {
                jQuery('#'+dropdown.attr('rel')).val(
                    jQuery(".active a", dropdown).attr('rel')
                );
            }

            var selectEl = document.getElementById(dropdown.attr('rel'));
            //raise change event on select - we use this and not jQuery trigger
            // because we need to trigger a native event
            if (selectEl.fireEvent) { // IE way
            	var e = document.createEventObject();
            	selectEl.fireEvent('onchange', e);
            }
            else { // DOM way
            	var e = document.createEvent('MouseEvents');
            	e.initEvent('change', true, true);
            	selectEl.dispatchEvent(e);
            }
        }

        dropdown.toggleClass('open');
        if (dropdown.hasClass('no-links') || isSizeSelect(dropdown)) {
            return false;
        }
    };
    /**
     * handle mouseleave event .dropdown
     */
    var dropdownMouseleave = function(e)
    {
        var target = jQuery(e.target);
        if(e.target.tagName.toLowerCase() != 'div') {
            var dropdown = target.parents('div');
        } else {
            var dropdown = target;
        }
        dropdown.removeClass('open');
    };

    return {
        init : function() { init(); },
        createFromSelect : function(el) {   createFromSelect(el); }
    }
}();

var Register = function() {

	/**
	 * Register.init()
	 * Initiate the print action for the parent frame
	 */
	var init = function()
	{
		if (!jQuery(".postcode-check-form").length) return false;
		jQuery(".postcode-check-form #postcode, .postcode-check-form #number").change(function(){
			validateAddress(".postcode-check-form");
		});

		jQuery(".postcode-check-form #address-use-foreign, .postcode-check-form #address-national").click(function(){
			useForeignAddress();
			return false;
		});

	};

	/**
	 * Register.useForeignAddress()
	 * Toggle between foreign / national inputs
	 *
	 * @param string prefix
	 * @return void
	 */
	var useForeignAddress = function(prefix)
	{
		jQuery(".register-toggle-elm").toggleClass('hide');
		jQuery(".for-nat-validate-toggle").toggleClass('required-entry');
		jQuery("#hidden-foreign-address").val(
			jQuery("#hidden-foreign-address").val() == 1 ? 0 : 1
		);
	};

	/**
	 * Register.validateAddress()
	 * Initiate the print action for the parent frame
	 *
	 * @param string prefix
	 * @return void
	 */
	var validateAddress = function(prefix)
	{
		var postcode = jQuery(prefix + ' #postcode').val();
		var num = jQuery(prefix + ' #number').val();
		postcode = postcode.toUpperCase().replace(/[^0-9A-Z]/g, '');
		num = num.replace(/[^0-9]/g, '');

		if (!postcode || !num) {
			return false;
		}

		if (!postcode.match(/^[0-9][0-9][0-9][0-9][A-Z][A-Z]$/)) {
			return false;
		}
		jQuery(".address-select").append(
			'<p class="loading-icon-wrap"><img src="/skin/frontend/kega/default/images/kega/loading.gif" alt="loading" class="loading-icon" /> Ophalen van adresinformatie</p>'
		);
		jQuery(".update-upon-validate input").addClass('hide');
		new Ajax.Request('/checkout/wizard/validateAddress', {
			method: 'post',
			parameters: {'address[postcode]':postcode,'address[number]':num},
			onSuccess: function(res) {

				jQuery(".loading-icon-wrap").remove();
				jQuery(".update-upon-validate input").removeClass('hide');
				
				var data = res.responseText;
				if (data) {
					data = jQuery.parseJSON(data);
					jQuery(prefix + ' #postcode').val(data.postcode);
					jQuery(prefix + ' #number').val(data.number);
					jQuery(prefix + ' #street').val(data.street);
					jQuery(prefix + ' #city').val(data.city);
				}
				else {
					jQuery(prefix + ' #street').val('');
					jQuery(prefix + ' #city').val('');
				}
			}
		});

		return false;
	};

	return {
		init : function() { init(); }
	};
}(jQuery);

var Store = (function($){

	var gmap;
	var infoWindow;

	/**
	 * Store.init
	 * Initializes the Google Map application
	 *
	 * @param void
	 * @return void
	 */
	var init = function()
	{
		if (!document.getElementById('map')) { return false; }

		gmap = new Map();
		gmap.init();
        gmap.addControl('show','large3D');
        gmap.addControl('type','menu');
		gmap.setTargetDirections('route');

		if (markers) {
			for(i=0, il=markers.length; i<il; i++) {
				gmap.addMarker(markers[i]);
			}
		}

		filter();
		toggleStoreInfo();
		submitStoreSearch();
	};


	/**
	 * Submit store search form
	 */
	var submitStoreSearch = function()
	{
		var input;
		var value;
		var placeholder;

		jQuery('#store-search').submit(function(e) {

			input = jQuery(this).find('#criteria');
			value = input.val();

			if (value == '') {
				// Prevent form submit
				e.preventDefault();
				input.addClass('validation-failed');
			}
		})
	};

	/**
	 * Toggle store info
	 */
	var toggleStoreInfo = function()
	{
		jQuery('.expand').click(function(e) {
			e.preventDefault();
			jQuery(this).closest('ul').find('li').removeClass('expanded');
			jQuery(this).closest('li').addClass('expanded');
		});

		/* toggle first item on default */
		$('li .expand', $('.store-list').not('.ignore')).first().click();
	};

	/**
	 * Toggle between 3 or all special openings
	 */
	var filter = function()
	{
		var count = 3;

		jQuery('.extra-openings').children().slice(count).hide();
		jQuery('.extra-openings-toggle').click(function(){
			jQuery('.extra-openings').children().show(200);
			jQuery(this).hide();
			return false;
		});
	};


	var findStores = function (search, radius) {

        if(!search && jQuery('#adr').val()) {
            search = jQyery('#adr').val();
        }

        if(!radius && jQuery('#radius').val()) {
            radius = jQuery('#radius').val();
        } else {
            radius = 10;
        }
		gmap.findStores(search, radius);

		return false;
    };

    var getMap = function () {
		return gmap;
    };

    var calculateDistance = function (from, to, appendObj) {
		var miledistance = from.distanceFrom(to, 3959).toFixed(1);
		var kmdistance = (miledistance * 1.609344).toFixed(1);
		$(appendObj).append('<span class="distance"> - '+ kmdistance + 'km');
    };

	return {
		init : function() {	init(); },
		findStores : function(search, radius) { findStores(search, radius); },
		calculateDistance : function(from, to, appendObj) { return calculateDistance(from, to, appendObj); },
		getMap : function() { return getMap(); }
	};
})(jQuery);