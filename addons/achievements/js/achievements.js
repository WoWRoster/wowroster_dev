$(document).ready(function () {
	$('#menu li').children('ul').slideUp('fast');
	$('#amain > div#s81').show();
	$('#a_menu li#s81').addClass("selected2");
	var parent;
	$('#menu li').click(function(e) {
		
		if ($(this).parents('li').size() > 0 ) 
		{
			//Has parent LI, so this is a child comment
			$(this).siblings().removeClass("selected2"); //Remove any "active" class
			$(this).addClass("selected2"); //Add "active" class to selected tab
			$('div#amain > div').hide();
			$("#amain > div#"+this.id).show();
			parent = true;
			return true;
		}
		else
		{
			if (!parent)
			{
				//Has no parent LI, top level comment
				$('div#amain > div').hide();
				$("li.menu_head").removeClass(" selected"); //Remove any "active" class
				$(this).addClass(" selected");
				//$('#menu li').children('ul').slideUp('fast');
				$('li.menu_head#'+this.id+' > ul#'+this.id+'_sub').siblings("ul.sub_menu").slideUp("slow");
				$("#amain > div#"+this.id).show();
				$('li.menu_head#'+this.id+' > ul#'+this.id+'_sub').slideToggle('400').siblings("ul.sub_menu").slideUp("slow");//.show();
			}
			parent=false;
		}
	});
	
	$('#a_menu li').click(function(e) {
		$('div#amain > div').hide();
		$(this).siblings().removeClass("selected2");
		$(this).addClass("selected2");
		$("#amain > div#"+this.id).show();
		
		$(this).siblings().children("ul.sub_menu").slideUp("fast");
		
		
		if($(this).siblings('ul#'+this.id+'_sub').length != 0)
		{
			$(this).siblings('ul#'+this.id+'_sub').slideToggle('400').siblings("ul.sub_menu").slideUp("slow");
		}
	});
	/*
	var achi = "'.implode(",",$achiv->pcrit).'";
    var arr = achi.split(',');
	jQuery.each(arr, function(index, value) {

		$("#crt" + value).addClass("ctunlocked");
		$("#lcrt" + value).addClass("unlocked");
		
	});
	*/

});