(function($){

	acf.fields.date_picker_fa = acf.field.extend({
		
		type: 'date_picker_fa',
		$el: null,
		$input: null,
		$hidden: null,
		
		o : {},
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize'
		},
		
		events: {
			'blur input[type="text"]': 'blur',
		},
		
		focus: function(){
			
			// get elements
			this.$el = this.$field.find('.acf-date_picker_fa');
			this.$input = this.$el.find('input[type="text"]');
			this.$hidden = this.$el.find('input[type="hidden"]');
			
			// get options
			this.o = acf.get_data( this.$el );
			
		},
		
		initialize: function(){
			
			// get and set value from alt field
			this.$input.val( this.$hidden.val() );
			
			
			// create options
 			/*var args = $.extend( {}, acf.l10n.date_picker, { 
				dateFormat		:	this.o.display_format,
				altField		:	this.$hidden,
				altFormat		:	'yymmdd',
				changeYear		:	true,
				yearRange		:	"-100:+100",
				changeMonth		:	true,
				showButtonPanel	:	true,
				firstDay		:	this.o.first_day
			}); */
			
			var $acf_fa_dp_format = this.o.display_format

			$('body').on('focus',".input.acf-persian-date-picker", function(){
				jQuery(this).datepicker({
					isRTL: true,
					dateFormat: $acf_fa_dp_format,
					onSelect: function(selectedDate) {
						/*$theGdate = jQuery(this).datepicker( 'getDate' ).getGregorianDate();
							var day = $theGdate.getDate();
							var monthIndex = $theGdate.getMonth();
							var year = $theGdate.getFullYear();
						jQuery(this).prev('.input-alt.acf-date_picker_fa_db').val(day+'/'+monthIndex+'/'+year)*/
						jQuery(this).prev('.input-alt.acf-date_picker_fa_db').val(selectedDate)
					 }
				});
			});			
		},
		
		blur : function(){
			
			if( !this.$input.val() ) {
			
				this.$hidden.val('');
				
			}
			
		}
		
	});
	
	

})(jQuery);