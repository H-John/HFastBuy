<link type="text/css" rel="stylesheet" href="catalog/view/HFastBuy/stylesheet/style.css" />
<script type="text/javascript" src="catalog/view/HFastBuy/javascript/js-css.js"></script>

<?php if (!$user_phone)  {?>
<script type="text/javascript" src="catalog/view/HFastBuy/javascript/jquery.maskedinput.min.js"></script>
<script type="text/javascript"><!--
$(function() {
		$("#recall").mask("+7 (999) 999 9999");
    });
//--></script> 
<?php } ?>

<div id="project-form-bg">
</div>
<div id="project-form-wrap">
<div id="project-form">
	<div class="rec_head">
		<h2><?php echo $text_entry; ?></h2>
		<p><?php echo $text_phone; ?></p>
	</div>
	<div class="rec_form">
		<input id="recall" name="ReqConTel" required class="form-control" placeholder="<?php echo $text_phoneholder; ?>" value="<?php echo $user_phone; ?>" />
		<input id="button-send-click" value="<?php echo $text_send; ?>" class="rec-form_button" type="submit" />
		<input id="product_id" value="<?php echo $product_id; ?>" type="hidden" />
	</div>
	<a class="closerec" title=""></a>
</div>
</div>
<button type="button" class="fiz" id="button-oneclick"><?php echo $text_entry; ?></button>


<script type="text/javascript"><!--
$(document).delegate('#button-send-click', 'click', function() {
    $.ajax({
        url: 'index.php?route=module/HFastBuy/save', 
        type: 'post',
        data: {"recall": $('#recall').val(), "input-quantity": $('#input-quantity').val(), "product_id": $('#product_id').val() },
        dataType: 'json',
        beforeSend: function() {
         	$('#button-send-click').button('loading');
		},  
        complete: function() {
            $('#button-send-click').button('reset');
        },          
        success: function(json) {
            $('.alert, .text-danger').remove();
            
			if (json['error']) {
                if (json['error']['warning']) {
                    $('.rec_form').prepend('<div class="alert alert-warning">' + json['error']['warning'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                }           
            } else {
                $('.rec_form').prepend('<div class="alert alert-success">' + json['success'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    }); 
});
//--></script> 