$(document).ready(function() {
	jQuery('.fiz').click(function() {
		$('#project-form-bg').fadeIn();
		$('#project-form-wrap').animate({ 
        'top': "20%",
	}, 500);
	});
	$(".closerec").click(function() {
		$('#project-form-wrap').animate({ 
        'top': "-120%",
	}, 500);
		$('#project-form-bg').fadeOut(300);
	});
	$("#project-form-bg").click(function() {
		$('#project-form-wrap').animate({ 
        'top': "-120%",
	}, 500);
		$('#project-form-bg').fadeOut(300);
	});
});