<?php
include('../report/inc/session.php');
include('../report/inc/conn.php');
include('../report/inc/auth.php');

$from = new DateTime(@$_GET['from']);
$to = new DateTime(@$_GET['to']);

$sql = 'select * from shifts';
$q = $conn->query($sql);
$shift = array();
while($r = $q->fetch(PDO::FETCH_ASSOC)){
	$shifts[] = array('id' => $r['shiftid'], 'name' => $r['shiftname']);
}
$shifts = json_encode($shifts);

?>
<html>
<head>
<title>Employee Attendance</title>
<link rel="stylesheet" type="text/css" href="inc/styles.css"/>
<?php include('inc/scripts.php'); ?>
<script src="inc/scripts.js"></script>
<script>
var shifts = <?php echo $shifts ?>;

function picker(elm){
	if ($('#picker').length == 0){
		$('body').append('<div id="picker"><ul></ul></div>')
			.click(function(e){
				$('#picker').removeClass('show');
			});
		$('#picker, #picker ul').click(function(e){
			e.stopPropagation();
		});
	}
	$(elm).click(function(e){
		e.stopPropagation();
		$('#picker').removeClass('show');
		var times = $(this).parents('tr').data('times').split(/, /g), pos = $(this).offset(), target = $(this).prev('input');
		$('#picker').data('target', target);
		$('#picker ul').empty();
		times.forEach(function(elm, idx, arr){
			$('#picker ul').append('<li>' + elm + '</li>');
		});
		$('#picker li').click(function(e){
			e.stopPropagation();
			$($('#picker').data('target')).val($(this).text()).parents('tr').addClass('changed');
			$('#picker').removeClass('show');
		});
		if ($('#picker').height() + pos.top + 30 > $(document).height()){
			$('#picker').css('transform-origin', '0 100%');
			pos.top -= $('#picker').height() + 30;
		} else {
			$('#picker').css('transform-origin', '');
		}
		$('#picker').css({top: pos.top + 30, left: pos.left - 80}).addClass('show');
	});
}

function attach_events(){
	picker('.time button');
	$('.time input').change(function(e){
		$(this).parents('tr').addClass('changed');
	});
	$('#post-form').submit(function(e){
		e.preventDefault();
		if ($(this).find('.changed input').length == 0){
			alert('Nothing to save');
			return;
		}
		//console.log($(this).find('.changed input').serialize(), $(this).find('.changed input'), this);
		$.post($(this).attr('action'), $(this).find('.changed input').serialize(), function(resp){
			if (resp.success){
				alert('Data saved');
			}
		}).error(function(a, b, c){
			console.log(a, b, c);
		});
	})
}
function load_data(norms, filter){
	$('.loading').removeClass('hidden');
	$('.list').addClass('hidden');

	var data;
	if (norms){
		data = {from: '<?php echo $from->format('Y-m-d') ?>', to: '<?php echo $to->format('Y-m-d') ?>', normal: 1};
	} else {
		data = {from: '<?php echo $from->format('Y-m-d') ?>', to: '<?php echo $to->format('Y-m-d') ?>'};
	}
	if (filter){
		data.filter = filter;
	}
	$.get('review_data.php', data, function(resp){
		$('.loading').addClass('hidden');
		$('.list').removeClass('hidden').html(resp);
		attach_events();
	});
}
$(window).ready(function(){
	$('#filter').change(function(e){
		e.stopPropagation();
		load_data($('#norms').prop('checked'), $('#filter').val());
	});
	$('#norms').click(function(e){
		e.stopPropagation();
		load_data($('#norms').prop('checked'), $('#filter').val());
	})
	load_data();
})
</script>
</head>
<body>
<?php
include('inc/menu.php');
?>

<div class="stretch">
<h1>Reviewing Attendance Between <?php echo $from->format('Y-m-d') ?> and <?php echo $to->format('Y-m-d') ?></h1>
<?php
$cname = 'MAKMURGROUP';
?>
<div class="filter">
<input type="text" id="filter"/><input type="checkbox" id="norms"/><label for="norms">Show Normal Attendance</label>
</div>
<div class="loading">Loading...</div>
<div class="list hidden"></div>
</div>
</body>
</html>