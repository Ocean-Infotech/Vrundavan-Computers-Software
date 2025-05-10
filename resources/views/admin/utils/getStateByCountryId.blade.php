<script>
	$('.search_by_country').change(function(){
		let country_id = $(this).val();
		let state_id = "{{ $edit->state_id ?? ''}}";
		let instance = $(this);
		let is_required = instance.attr('required');
		let is_select2 = instance.hasClass('select2', null);
		let is_searchable = instance.attr('data-choices');

		if(!state_id){ state_id = instance.attr("data-state_id"); }
		if(country_id){
			$.ajax({
				type:'GET',
				url:'{{ env("APP_API_URL") }}get-state',
				'beforeSend': function (request) {
					request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
				},
				data: {
					country_id : country_id
				},
				success:function(response) {
					if(response.status){
						console.log("getStateByCountryId 22",response.data, state_id);
						let options = "<option value='' selected>Select State</option>";
						if(response.data && response.data.length >0 ){
							$.each(response.data, function(i, item) {
								if(state_id == item.id){
									options += "<option value='"+item.id+"' data-country_id='"+item.country_id+"' selected >"+item.name+"</option>";
								}else{
									options += "<option value='"+item.id+"' data-country_id='"+item.country_id+"' >"+item.name+"</option>";
								}
							});
						}
						console.log("getStateByCountryId 33", instance.parent(), instance.parent().parent());
						if(instance.attr('data-append')){
							// && instance.attr('data-append') == "append_state_data"
							if($(document).find("."+instance.attr('data-append')).length > 0){
								$("."+instance.attr('data-append')).empty();
								$("."+instance.attr('data-append')).append(options);
							}else{
								$("."+instance.attr('data-append')).empty();
								$("."+instance.attr('data-append')).append(options);
							}
						}else if(instance.parent().parent().hasClass("col-md-6") || instance.parent().parent().hasClass("col-md-4") || instance.parent().parent().hasClass("col-md-3")){
                            console.log("getStateByCountryId 44", instance);

							let select_tag = '<select name="state_id" class="form-select search_by_state';
							if(is_select2){ select_tag += ' select2 '; }
							if(is_searchable){ select_tag += ' data-choices data-choices-sorting="true"'; }
							select_tag += '"';
							if(is_required){ select_tag += ' required '; }
							select_tag += '>'+options+'</select>';
							let html = '';
							if(instance.parent().parent().hasClass("col-md-4")){
								html = '<div class="col-md-4 state_div">';
							}else if(instance.parent().parent().hasClass("col-md-3")){
								html = '<div class="col-md-3 state_div">';
							}else{
								html = '<div class="col-md-6 state_div">';
							}
							html += '<div class="form-group"> <label class="form-label" for="state_id">Select State ';
							if(is_required){
								html += '<span class="text-danger">*</span>';
							}
							html += '</label>';
							html += select_tag;
							html += '</div></div>';
							$(".state_div").remove();
							instance.parent().parent().after(html);
						}else if(instance.find(".search_by_state")){
                            console.log("getStateByCountryId 71", instance, instance.find(".search_by_state"));
							$(".search_by_state").removeClass('d-none').empty().append(options);
							/*
							// $(".search_by_state").remove();
							// instance.parent().next("th").remove();
							// instance.parent().after('<th>'+options+'</th>');
							*/
						}else if(instance.next("tr")){
                            console.log("getStateByCountryId 71", instance, instance.parent(), instance.next("th"));
							$(".search_by_state").removeClass('d-none').empty().append(options).trigger('change');
							/*
							// $(".search_by_state").remove();
							// instance.parent().next("th").remove();
							// instance.parent().after('<th>'+options+'</th>');
							*/
						}else {
                            console.log("getStateByCountryId 79", instance, instance.parent(), instance.next("th"));

							instance.parent().after(options);
						}
						$('.search_by_state').trigger("change");
						$('.select2').select2();
					}
               }
            });
		}
	});
	$('.search_by_country').trigger("change");
</script>
