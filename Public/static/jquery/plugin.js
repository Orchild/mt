(function($){
$.fn.overlay 			= function(options){
	var action 			= options == 'hide' ? 'hide' : 'show' ;
	var self 			= this;
	var position		= function(){
		var overlay		= self.find('>.overlay');
		var loading		= self.find('>.loading');
		if(self.is('body')){
			overlay.css('position','fixed');
			loading.css('position','fixed');
		}else{
			self.css('position','relative');
			overlay.css('position','absolute');
			loading.css('position','absolute');
		}
		overlay.css({
			'top'		: 0,
			'bottom'	: 0,
			'left'		: 0,
			'right'		: 0,
			'z-index'	: 20000,
			'opacity'	: 0.5
		});
		var w 			= overlay.outerWidth(true);
		var h 			= overlay.outerHeight(true);
		loading.css({
			'top'		: h / 2 - loading.outerHeight(true) / 2,
			'left'		: w / 2 - loading.outerWidth(true) / 2,
			'z-index'	: 20001
		});
	}
	if(self.is(':has(>.overlay)')){
		self.find('>.overlay,>.loading')[action]();
		position();
	}else{
		self.css("position","relative").append('<div class="overlay"></div><div class="loading"></div>');
		position();
	}
}
$.overlay 			= function(){
	$('body').overlay();
}
$.unoverlay			= function(){
	$('body').overlay('hide');
}
$.fn.verify 		= function(){
	return this.each(function(){
		var self	= $(this);
		var src 	= self.attr("src");
		self.click(function(){
			if(src.indexOf('?')>0){
				self.attr("src",src+'&random='+Math.random());
			}else{
				self.attr("src",src.replace(/\?.*$/,'')+'?'+Math.random());
			}
		});
	});
}
})(jQuery);
(function($){
    $.extend({
        "autoImage" : function(options){   
            options = options || {};
            options = $.extend({
                "parent"         : null,
                "loadClassId"    : "loading",
                "src"            : "",
				"url"            : "",
				"alt"            : ""
            },options);
            var parent = options.parent;
            if(parent == null || parent.length == 0) return;
            parent.addClass(options.loadClassId);
			var url = (options.url) ? options.url : options.src;
            if(options.src) parent.html("<a href='"+url+"' title='"+options.alt+"' target='_blank'><img alt='"+options.alt+"' /></a>");
            var image   = parent.find("img");
            var src     = (options.src) ? options.src : image.attr("src");
            var img     = new Image();
            img.src     = src;   
   
            if(img.complete){
                image.attr("src",img.src);
                parent.removeClass(options.loadClassId);
                imgAuto();
                return;
            }
            img.onerror   = function(){
                parent.removeClass(options.loadClassId);
                parent.addClass('imgerror');
            }
            image.attr("src","");
            image.hide();
            $(img).load(function(){
                imgAuto();
                image.attr("src",this.src);
                image.fadeIn("normal",function(){
                    parent.removeClass(options.loadClassId);   
                });
            });   
   
            function imgAuto(){    
                var width   = img.width;
                var height  = img.height;
                var pwidth  = parent.width();
                var pheight = parent.height();
                if(width>0 && height>0){
                    var rate = (pwidth/width < pheight/height) ? pwidth/width : pheight/height;
                    if(rate <= 1){
                        width  *=  rate;
                        height *=  rate;
                    }
                    var left = (pwidth - width) * 0.5;
                    var top  = (pheight - height) * 0.5;
                    image.css({
                        "margin-left" : left + "px",
                        "margin-top"  : top + "px",
                        "width"       : width + "px",
                        "height"      : height + "px"
                    });
                }
            }
   
        }
    });
    $.fn.extend({
        "autoImage" : function(src,url,alt,loadClassId){
            return this.each(function(){       
                $.autoImage({
                    "parent"      : $(this),
                    "src"         : src,
					"url"         : url,
					"alt"         : alt,
                    "loadClassId" : loadClassId
                });
            });
        }
    });
})(jQuery);