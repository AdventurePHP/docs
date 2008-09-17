$(document).ready(function(){

    /**
     * Zuerst alle Tabs, die nicht sichtbar sein sollen, ausblenden
     */
     
    //#left_box_content
    $("div#left_box_content > div:not(div#tab-references)").css("display", "none");
    
    //#right_box_content
    $("div#right_box_content > div:not(div#tab-news)").css("display", "none");
    
    
    /**
     * EventHandler hinzufŸgen
     */
    
    //#left_box_content
    
    $("div#left_box_content > h3 > span").bind("click", function(){
    	//anderen h3's Klasse active wegnemen
    	$("div#left_box_content > h3").removeClass("active");
    	//Klasse active hinzufŸgen
    	$(this).parent().attr("class", "active");
    	
    	var tabName = "tab-" + $(this).attr("class");
    	toggleTabLeft(tabName);
    });
    
    function toggleTabLeft(name) {
    	$("div#left_box_content > div:not(div#"+name+")").css("display", "none");
    	$("div#left_box_content > div#"+name).css("display", "block");
    }
    
    //#right_box_content
    
    $("div#right_box_content > h3 > span").bind("click", function(){
    	//anderen h3's Klasse active wegnemen
    	$("div#right_box_content > h3").removeClass("active");
    	//Klasse active hinzufŸgen
    	$(this).parent().attr("class", "active");
    	
    	var tabName = "tab-" + $(this).attr("class");
    	toggleTabRight(tabName);
    });
    
    function toggleTabRight(name) {
    	$("div#right_box_content > div:not(div#"+name+")").css("display", "none");
    	$("div#right_box_content > div#"+name).css("display", "block");
    }
    
});