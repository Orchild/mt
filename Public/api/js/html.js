window.onload = function(){
	var width = document.body.clientWidth;
	var height = width*0.5625+"px";
	//头部视频大小调节，适应屏幕
	var item = document.getElementsByTagName("iframe");
	if(item.length>0){
		item[0].setAttribute("width",width+"px");						
		item[0].setAttribute("height",height);
	}
	//文章中图片大小调节，大于屏幕宽度的缩小至屏宽，小于的不做调整
	var img = document.getElementsByTagName("img");
	if(img.length>0){
		for(var i=0;i<img.length;i++){
			if(img[i].getAttribute("width")>width){
				img[i].setAttribute("width",width+"px");
				img[i].setAttribute("height","");
				img[i].setAttribute("style","border: 0px; display: block; margin: 0px auto;max-width:"+width+"px;");
			}else{
				var style = img[i].getAttribute("style");
				img[i].setAttribute("style",style+";max-width:"+width+"px;");
			}
		}
	}
	
	var tables = document.getElementsByTagName('table');
	if(tables.length>0){
		for(i=0;i<tables.length;i++){
			tables[i].setAttribute("width","100%");
			tables[i].setAttribute('cellspacing','0');
			tables[i].setAttribute('cellpadding','0');
		}
	}
}