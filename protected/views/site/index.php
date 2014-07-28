<?php
/* @var $this SiteController */
$this->pageTitle = "iOS constructor prototype";
?>


<script type="text/javascript">

$(document).ready(function(){

	
    function changeBackground() {
        $( '#screen' ).css( { backgroundImage: 'url(uploads/screen.png)' } );
    }

    setInterval( changeBackground, 2000 );  
});


</script>


<div class="iphone">

	<div id="screen" class="screen"></div>

	<?php
	/* @var $this SiteController */
	echo "v".Yii::app()->session['var'];
	
?>
	
	
	
</div>
