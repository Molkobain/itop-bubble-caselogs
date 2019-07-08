/*
 * Copyright (c) 2015 - 2019 Molkobain.
 *
 * This file is part of licensed extension.
 *
 * Use of this extension is bound by the license you purchased. A license grants you a non-exclusive and non-transferable right to use and incorporate the item in your personal or commercial projects. There are several licenses available (see https://www.molkobain.com/usage-licenses/ for more informations)
 */

;
$(function()
{
    $.widget('molkobain.bubble_caselogs',
        {
            options: {
                debug: false,
                endpoint: null,
                gui: 'console',
            },

            // Constructor
            _create: function()
            {
                this._super();

                this.element.addClass('molkobain-bubble-caselogs');

                // Initiliazing widget
                this.initialize();
            },
            // Events bound via _bind are removed automatically
            // Revert other modifications here
            _destroy: function()
            {
                this.element.removeClass('molkobain-bubble-caselogs');

                this._super();
            },
            // _setOptions is called with a hash of all options that are changing
            // Always refresh when changing options
            _setOptions: function()
            {
                this._superApply(arguments);
            },
            // _setOption is called for each individual option that is changing
            _setOption: function(key, value)
            {
                this._super(key, value);
            },
            // Display trace in js console
            _trace: function(sMessage)
            {
                if(window.console && this.options.debug === true)
                {
                    console.log('Molkobain bubble caselogs: ' + sMessage);
                }
            },

            // Initialize the widget
            initialize: function()
            {
                var me = this;

                me._showLoader();
                $.post(
                    this._getEndpoint(),
                    {
                        gui: this.options.gui,
                        att_code: this._findAttCode(),
                    }
                )
                 .done(function(oData)
                 {
                     me.element.html(oData);

                     // Tooltips
                     if(me.options.gui === 'console')
                     {
	                     me.element.find('.mbc-thread [data-toggle="tooltip"]').qtip({ style: { name: 'molkobain-dark', tip: 'bottomMiddle', classes: { content: 'mbc-text-align' } }, position: { corner: { target: 'topMiddle', tooltip: 'bottomMiddle' }, adjust: { y: -20 }} });
                     }
                     else
                     {
	                     me.element.find('.mbc-thread [data-toggle="tooltip"]').tooltip();
                     }

                     // Entry toggle
                     // - Open entry on click
                     me.element.on('click', '.mbc-tcb-entry.closed', function()
                     {
                         $(this).removeClass('closed');
                     });
                     // - Close entry on toggler click
                     me.element.on('click', '.mbc-tcb-entry .mbc-tcbe-toggler', function()
                     {
                         $(this).closest('.mbc-tcb-entry').toggleClass('closed');
                     });
                     // - Toggle all entries on click
                     me.element.on('click', '.mbc-tht-openall', function(oEvent)
                     {
                         oEvent.preventDefault();
                         me.element.find('.mbc-tcb-entry').removeClass('closed');
                     });
                     me.element.on('click', '.mbc-tht-closeall', function(oEvent)
                     {
                         oEvent.preventDefault();
                         me.element.find('.mbc-tcb-entry').addClass('closed');
                     });
                 })
                 .always(function()
                 {
                     me._hideLoader();
                 });

                // Note: We do nothing on fail, the legacy caselog is still displayed.
            },

            // Getters
            _getEndpoint: function()
            {
                return this.options.endpoint;
            },

            // Helpers
            _showLoader: function(){
                this.element.append( $('<div class="mbc-loader"><span class="fa fa-refresh fa-spin fa-fw fa-3x"></span></div>') );
            },
            _hideLoader: function()
            {
                this.element.find('.mbc-loader').remove();
            },
            _findAttCode: function()
            {
                // Note: We could have make derivated widgets and overload this method to specialize each widgets...
                // Console in view mode
                if(this.element.closest('.caselog').parent()
                       .find('input[type="hidden"][name^="attr_"]:first').length > 0)
                {
                    return this.element.closest('.caselog').parent().find('input[type="hidden"][name^="attr_"]:first')
                               .attr('name')
                               .slice(5);
                }
                // Console in edit mdoe
                else if(this.element.closest('[data-attcode]').length > 0)
                {
                    return this.element.closest('[data-attcode]').attr('data-attcode');
                }
                // Portal
                else if(this.element.closest('[data-field-id]').length > 0)
                {
                    return this.element.closest('[data-field-id]').attr('data-field-id');
                }
            },
        }
    );
});
