var reprocess = false, hris = false;

$(window).ready(function(){
	$('table #all').click(function(e){
		var state = $(this).prop('checked');
		$('table input[type=checkbox]:not(#all)').prop('checked', state);
	});
	$('table input[type=checkbox]:not(#all)').change(function(e){
		if ($('table input[type=checkbox]:not(#all)').length == $('table input[type=checkbox]:not(#all):checked').length){
			$('table #all').prop('checked', true);
		} else {
			$('table #all').prop('checked', false);
		}
	});
	
	$('#btn-download').click(function(e){
		if ($('table input[type=checkbox]:not(#all):checked').length == 0){
			alert('Nothing to download');
			return;
		}
		$('table input[type=checkbox]:not(#all):checked').each(function(idx, elm){
			var mch = elm.id;
			$('#st-' + mch).html('<div class="downloading">downloading...</div>');
			$.ajax({
				url: '/pull.php',
				type: 'GET',
				data: {mch: mch},
				dataType: 'json',
				timeout: 1800000,
				success: function(resp){
					if (resp.success){
						$('#st-' + mch).text(resp.last_download);
					} else {
						$('#st-' + mch).text('Download failed: ' + resp.error);
					}
					$('table #all').prop('checked', false);
					$(elm).prop('checked', false);
				}
			}).error(function(){
				//server / network error
				$('#st-' + mch).text('Download failed: unable to connect');
				$('table #all').prop('checked', false);
				$(elm).prop('checked', false);
			});	/**/

/*			$.get('/pull.php', {mch: mch}, function(resp){
				if (resp.success){
					$('#st-' + mch).text(resp.last_download);
				} else {
					$('#st-' + mch).text('Download failed: ' + resp.error);
				}
				$('table #all').prop('checked', false);
				$(elm).prop('checked', false);
			}).error(function(){
				//server / network error
				$('#st-' + mch).text('Download failed: unable to connect');
				$('table #all').prop('checked', false);
				$(elm).prop('checked', false);
			});	/**/
		});
	});
	
	$('#btn-clear').click(function(e){
		if ($('table input[type=checkbox]:not(#all):checked').length == 0){
			alert('Nothing to clear');
			return;
		}
		if (confirm('Are you sure you want to clear selected machine data?')){
			$('table input[type=checkbox]:not(#all):checked').each(function(idx, elm){
				var mch = elm.id;
				$('#st-' + mch).html('<div class="downloading">Cleaning...</div>');
				$.ajax({
					url: '/clear.php',
					type: 'GET',
					data: {mch: mch},
					dataType: 'json',
					timeout: 1800000,
					success: function(resp){
						if (resp.success){
							$('#st-' + mch).text('Cleaning done.');
						} else {
							$('#st-' + mch).text('Cleaning failed: ' + resp.error);
						}
						$('table #all').prop('checked', false);
						$(elm).prop('checked', false);
					}
				}).error(function(){
					//server / network error
					$('#st-' + mch).text('Cleaning failed: unable to connect');
					$('table #all').prop('checked', false);
					$(elm).prop('checked', false);
				});	/**/
			});
		}
	});
	
	$('#btn-cal-prc, #btn-post, #btn-post-hris').click(function(e){
		e.preventDefault();
		reprocess = true;
		if (this.id == 'btn-post-hris'){
			hris = true;
		} else {
			hris = false;
		}
		$('.date-popup, .veil').addClass('show');
	});
	
	$('#btn-cal-rvw').click(function(e){
		e.preventDefault();
		reprocess = false;
		$('.date-popup, .veil').addClass('show');
	});
	
	$('#btn-process').click(function(e){
		e.preventDefault();
		location.href = (reprocess ? (hris ? 'process-hris.php' : 'process.php') : 'attendance.php') + '?from=' + $('#opt_from').val() + '&to=' + $('#opt_to').val() + ($('#test_only:checked').length == 1 ? '&test=1' : '');
	});
});