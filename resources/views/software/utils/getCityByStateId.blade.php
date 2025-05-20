<script>
	$(document).on('change', '.search_by_state', function(){
		let instance = $(this);
		let country_id = $(this).find(':selected').attr("data-country_id");
		let state_id = $(this).val();
		let is_required = instance.attr('required');
		let is_select2 = instance.hasClass('select2');
		let city_id = "";

		if(!country_id){
			let country_id = $('.search_by_country').val();
		}

		if(state_id.length == 0){
			state_id = $('.search_by_country').attr("data-state_id");
		}

		if(city_id.length == 0){
			city_id = $('.search_by_country').attr("data-city_id");
		}

		// console.log("L-17 CityByState",state_id, city_id, $('.search_by_country').attr("data-city_id"));
		if(country_id && state_id){
			$.ajax({
				type:'GET',
				url:'{{ env("APP_API_URL") }}get-cities',
				'beforeSend': function (request) {
					request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
				},
				data: {
					country_id : country_id,
					state_id : state_id
				},
				success:function(response) {
					if(response.status){

						let options = "<option value='' selected>Select City</option>"
						if(response.data && response.data.length >0 ){
							$.each(response.data, function(i, item) {
								if(city_id == item.id){
									options += "<option value='"+item.id+"' data-country_id='"+item.country_id+"' data-state_id='"+item.state_id+"' selected >"+item.name+"</option>";
								}else{
									options += "<option value='"+item.id+"' data-country_id='"+item.country_id+"' data-state_id='"+item.state_id+"' >"+item.name+"</option>";
								}
							});
						}
						// console.log("getCityByStateId 33",instance.parent());
						if(instance.attr('data-append')){
							// && instance.attr('data-append') == "append_city_data"
							if($(document).find("."+instance.attr('data-append')).length > 0){
								$("."+instance.attr('data-append')).empty();
								$("."+instance.attr('data-append')).append(options);
							}else{
								$("."+instance.attr('data-append')).empty();
								$("."+instance.attr('data-append')).append(options);
							}
						}else if(instance.parent().parent().hasClass("col-md-6") || instance.parent().parent().hasClass("col-md-4") || instance.parent().parent().hasClass("col-md-3")){
							let select_tag = '<select name="city_id" class="form-select search_by_city';
							if(is_select2){ select_tag += ' select2 '; }
							select_tag += '"';
							if(is_required){ select_tag += ' required '; }
							select_tag += '>'+options+'</select>';

							let html = '';
							if(instance.parent().parent().hasClass("col-md-4")){
								html = '<div class="col-md-4 city_div">';
							}else if(instance.parent().parent().hasClass("col-md-3")){
								html = '<div class="col-md-3 city_div">';
							}else{
								html = '<div class="col-md-6 city_div">';
							}
							html += '<div class="form-group"> <label class="form-label" for="city_id">Select city</label>';
							if(is_required){
								html += '<span class="text-danger">*</span>';
							}
							html += '</label>';
							html += select_tag;
							html += '</div></div>';
							$(".city_div").remove();
							instance.parent().parent().after(html);
							$('.select2').select2();
						}else if(instance.parent("tr")){
							// console.log("getCityByStateId 39", instance, instance.parent(), instance.next("th"));
							$(".search_by_city").removeClass('d-none').empty().append(options);
							/*
							// $(".search_by_city").remove();
							// instance.parent().next("th").remove();
							// instance.parent().after('<th>'+options+'</th>');
							*/
						}else{
							instance.parent().after(options);
						}
					}
					$('.select2').select2();
					$('.search_by_city').trigger("change");
				}
			});
		}
	});
	$('.search_by_state').trigger("change");
</script>
