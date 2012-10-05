jQuery(document).ready(function(){
    Wizard.init();
});

/**
 * Wizard
 *
 */
var Wizard = function()
{
    /**
     * Wizard.init
     * trigger init process for wizard steps
     *
     * @param void
     * @return void
     */
    var init = function()
    {
        initAjaxCart();
        initCart();
        initDelivery();
        initPayment();
    };

    /**
     * Wizard.initCart
     * Update cart button visibility toggle
     *
     * @param void
     * @return void
     */
    var initCart = function()
    {
        var toggle = jQuery("#update-cart-wrap");
        toggle.hide();
        jQuery("#shopping-cart-table .qty, #shopping-cart-table .product-cart-option").bind('change keyup',function(){
            toggle.show();
        });
		jQuery("#shopping-cart-table .dropdown").click(function(){
			toggle.show();
		});

		jQuery('.mini-cart').hover(function(){
			jQuery('.mini-cart-wrap').addClass('mini-cart-wrap-active');
		}, function(){
			setTimeout(function(){
				jQuery('#mini-products-list').fadeOut('medium', function(){
					jQuery('.mini-cart-wrap').removeClass('mini-cart-wrap-active');
					jQuery('#mini-products-list').removeAttr('style');
				});
			}, 4000);
		});

		amountChooser();
    };

    /**
	 * Find .amount-chooser items and add amount chooser to it.
	 */
	var amountChooser = function()
	{
		var parent, c, ac, amount;
		jQuery('.amount-chooser').each(function(){
			parent = jQuery(this).parent();

			parent.append('<span class="amount-chooser-wrap"><span class="up"></span><span class="down"></span></span>');
			jQuery('.amount-chooser-wrap', parent).append($(this));
		});

		jQuery('.amount-chooser-wrap').click(function(e){
			c = jQuery(e.target).attr('class');
			ac = jQuery('.amount-chooser', this);
			if (c == 'up') {
				amount = parseInt(ac.attr('value')) + 1;
			}
			else if (c == 'down') {
				if (ac.attr('value') > 0) {
					amount = parseInt(ac.attr('value')) - 1;
				}
			}
			else {
				return;
			}
			ac.attr('value', amount);
			jQuery("#update-cart-wrap").show();

			return false;
		});
	};

    /**
     * Wizard.initAjaxCart
     * Overwrite cart post url, start AJAX cart transaction
     * NOTE: we overwrite the url here so the cart still works when no JS is available
     *
     * @param void
     * @return void
     */
    var initAjaxCart = function()
    {
        jQuery(".product_addtocart_form").each(function() {
            form = jQuery(this);

            var action = form.attr('action');
            action = action.replace('checkout', 'wizard');
            form.attr('action', action);

            form.submit(function(){
                addProductXHR(this);
                return false;
            });
        });

        jQuery('.close-colorbox').live('click', function(){
            jQuery.colorbox.close();
        });
    };

    /**
     * Wizard.addProductXHR
     * Send add to cart action back to server
     *
     * @param void
     * @return void
     */
    var addProductXHR = function(form)
    {
		if (jQuery('body').hasClass('catalog-product-view')) {
			if(jQuery('#attribute140').size() && !jQuery('#attribute140').val()) {
				jQuery('.size-error').remove();
				jQuery('#product-options-wrapper h2').append('<span class="size-error">Dit is een verplicht veld!</span>');
				jQuery('.size-error').fadeIn('slow');
				return false;
			}
		}

        var patt = new RegExp(/attribute([0-9]+)/);
        var result = patt.exec(jQuery(".super-attribute-select", form).attr('id'));

		if (result === null) {
			result = patt.exec(jQuery(".super-attribute-select", form).attr('rel'));
		}

        cartXHRprogress();

        data = {};
        if (result && result[1]) {
            data.super_attribute_id = result[1];
            data.super_attribute_value = jQuery(".super-attribute-select", form).val();
        }

        jQuery.ajax({
            'url': form.action,
            'data': data,
            'dataType': 'html',
            'type': 'post',
            'success': function(response) {

                if (response == 'FAILED') {
                    var div = '<div id="xhr-cart-fail">';
                    div += '<h3>Product kan niet worden toegevoegd<a href="#" class="close-colorbox">Sluiten [x]</a></h3>';
                    div += '<div class="xhr-cart-progress-content">';
                    div += '<p class="error-msg">Het opgegeven product kan momenteel niet aan je winkelwagen worden toegevoegd.</p>';
                    div += '<p><a href="#" id="back-to-product">&laquo; Terug naar het product</a></p>';
                    div += '</div>';
                    div += '</div>';
                    updateXHRprogress(div);

                    jQuery("#back-to-product").click(function(){
                        jQuery("#cboxClose").trigger('click');
                        return false;
                    });
                }
                else {
                	/**
                	 *  Delete divs from response for hover event to keep working
                	 *  .live() was not working in initCart function
                	 */

                	// Delete divtags of response
                	response = response.replace("<div class=\"mini-cart-wrap\">", "");
                	response = response.replace("<div class=\"mini-cart\">", "");
                	// Delete ending divtags of response
                	var lastDiv = response.lastIndexOf("</div>");
                	response = response.substring(0, lastDiv);
                	var lastDiv = response.lastIndexOf("</div>");
                	response = response.substring(0, lastDiv);

                	// Delete content of mini-cart
                	jQuery(".mini-cart").empty();

                	// Insert cart into mini-cart
                	jQuery(".mini-cart").html(response);

                    updateXHRprogress(jQuery("#mini-products-list").html());
                }
            },
            'error': function(XMLHttpRequest, textStatus, errorThrown){}
        });
    };

    /**
     * Wizard.cartXHRprogress
     * Display cart progress on screen, block all other interaction
     *
     * @param void
     * @return void
     */
    var cartXHRprogress = function()
    {
        var div = '<div id="xhr-cart-progress">';
        div += '<div class="xhr-cart-progress-content">';
        div += '<p class="loading-msg">';
        div += 'Het product wordt aan je winkelwagen toegevoegd..</p>';
        div += '</div>';
        div += '</div>';
        jQuery("body").append(div);

        jQuery("#xhr-cart-progress").colorbox({
            width:"500px",
            height:"100px",
            inline:true,
            scrolling:false,
            href:"#xhr-cart-progress",
            onClosed:function(){ jQuery("#xhr-cart-progress").remove(); }
        });
        jQuery("#xhr-cart-progress").trigger('click');
        jQuery("#xhr-cart-progress").removeClass('cboxElement');

        jQuery("#cboxClose").hide();
    };

    /**
     * Wizard.updateXHRprogress
     * Updates the content of the XHR progress message
     *
     * @param void
     * @return void
     */
    var updateXHRprogress = function(content)
    {
		jQuery("#cboxClose").trigger('click');
		jQuery('.remove-item').addClass('hide');
		jQuery('.mini-cart-wrap').addClass('mini-cart-wrap-active');

		setTimeout(function(){
			jQuery('#mini-products-list').fadeOut('medium', function(){
				jQuery('.mini-cart-wrap').removeClass('mini-cart-wrap-active');
				jQuery('#mini-products-list').removeAttr('style');
			});
		}, 4000);
	};

    /**
     * Wizard.closeXHRprogress
     * Closes colorbox overlay, removes XHR cart progress HTML from source
     *
     * @param void
     * @return void
     */
    var closeXHRprogress = function()
    {
        jQuery("#xhr-cart-progress").remove();
        jQuery("#cboxClose").trigger('click');
    };

    /**
     * Wizard.initDelivery
     * Toggle for content per delivery option
     *
     * @param void
     * @return void
     */
    var initDelivery = function()
    {
        var li;
        jQuery(".expand-content").hide();
        jQuery(".expand-content-option").each(function(){
            jQuery(this).click(function(){
                li = jQuery(this).parents('li').eq(0);
                jQuery(".expand-content").hide();
				jQuery(li).parent().children('li').removeClass('active');
                jQuery(".expand-content", li).show();
				jQuery(li).addClass('active');
            });

            if (jQuery(this).is(":checked")) {
                li = jQuery(this).parents('li').eq(0);
                jQuery(".expand-content", li).show();
				jQuery(li).addClass('active');
            }
        });

    };

    /**
     * Wizard.initPayment
     * Set up terms colorbox
     *
     * @param void
     * @return void
     */
    var initPayment = function()
    {
        if(!jQuery('.checkout-wizard-payment').size()) {
            return;
        }

        jQuery('#agreement-wrap a').colorbox({
            inline:true,
            height:"400px",
            width: "500px",
            href:"#terms",
            onOpen:function(){ jQuery("#terms").removeClass('no-display'); },
            onCleanup:function(){ jQuery("#terms").addClass('no-display'); }
        });

        // disable complete payment button after click
        jQuery('#payment-methods p button').click(function(){
        	jQuery(this).css('display', 'none');
			jQuery(this).parent().children('.load-payment').css('display', 'block');
        	return true;
        });

		ValidatePayment();
    };

	var ValidatePayment = function () {
		var submit = $('#payment-methods button[type="submit"]'),
			method = $('input[name="payment[method]"][value="ogone"]'),
			notice = $('#payment-methods .messages'),
			paymentBlock = $('#wizard-payment-details');

		submit.click(function (event) {
			var type = $('input[name="payment[cc_type]"]:checked');
			if (type.length === 0 && method.is(':checked')) {
				// show notice if cc_type is not specified
				event.preventDefault();
				notice.show();
				// scroll notice into viewport
				$('html, body').animate({
					scrollTop: paymentBlock.offset().top
				}, 1000);
				return false;
			} else {
				// disable complete payment button after click
				submit.hide();
				notice.hide();
				submit.parent().children('.load-payment').show();
				return true;
			}
		});
	}

    return {
        init : function() {
            init();
        }
    }
}();

// payment
var Payment = Class.create();
Payment.prototype = {
    initialize: function(form, saveUrl){
        this.form = form;
    },

    init : function () {
        var elements = Form.getElements(this.form);
        if ($(this.form)) {
            $(this.form).observe('submit', function(event){this.save();Event.stop(event);}.bind(this));
        }
        var method = null;
        for (var i=0; i<elements.length; i++) {
            if (elements[i].name=='payment[method]') {
                if (elements[i].checked) {
                    method = elements[i].value;
                }
            } else {
                elements[i].disabled = true;
            }
        }
        if (method) this.switchMethod(method);
    },

    switchMethod: function(method){
        if (this.currentMethod && $('payment_form_'+this.currentMethod)) {
            var form = $('payment_form_'+this.currentMethod);
            form.style.display = 'none';
            var elements = form.select('input', 'select', 'textarea');
            for (var i=0; i<elements.length; i++) elements[i].disabled = true;
        }
        if ($('payment_form_'+method)){
            var form = $('payment_form_'+method);
            form.style.display = '';
            var elements = form.select('input', 'select', 'textarea');
            for (var i=0; i<elements.length; i++) elements[i].disabled = false;
            this.currentMethod = method;
        }
    },

    validate: function() {
        var methods = document.getElementsByName('payment[method]');
        if (methods.length==0) {
            alert(Translator.translate('Your order can not be completed at this time as there is no payment methods available for it.'));
            return false;
        }
        for (var i=0; i<methods.length; i++) {
            if (methods[i].checked) {
                return true;
            }
        }
        alert(Translator.translate('Please specify payment method.'));
        return false;
    },

    save: function(){
        var validator = new Validation(this.form);
    }
}
payment = new Payment();