

var detectZoom = function(){ 
    var ratio = 0,
        screen = window.screen,
        ua = navigator.userAgent.toLowerCase();

    if( ~ua.indexOf('firefox') ){
        if( window.devicePixelRatio !== undefined ){
            ratio = window.devicePixelRatio;
        }
    }
    else if( ~ua.indexOf('msie') ){    
        if( screen.deviceXDPI && screen.logicalXDPI ){
            ratio = screen.deviceXDPI / screen.logicalXDPI;
        }
    }
    else if( window.outerWidth !== undefined && window.innerWidth !== undefined ){
        ratio = window.outerWidth / window.innerWidth;
    }
    
    if( ratio ){
        ratio = Math.round( ratio * 100 );
    }    
   
    return ratio;
};
