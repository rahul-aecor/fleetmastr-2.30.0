/*
 * jquery.flot.tooltip
 * 
 * description: easy-to-use tooltips for Flot charts
 * version: 0.8.5
 * authors: Krzysztof Urbas @krzysu [myviews.pl],Evan Steinkerchner @Roundaround
 * website: https://github.com/krzysu/flot.tooltip
 * 
 * build on 2015-06-12
 * released under MIT License, 2012
*/ 
!function(a){var b={tooltip:{show:!1,cssClass:"flotTip",content:"%s | X: %x | Y: %y",xDateFormat:null,yDateFormat:null,monthNames:null,dayNames:null,shifts:{x:10,y:20},defaultTheme:!0,snap:!0,lines:!1,onHover:function(a,b){},$compat:!1}};b.tooltipOpts=b.tooltip;var c=function(a){this.tipPosition={x:0,y:0},this.init(a)};c.prototype.init=function(b){function c(a){var c={};c.x=a.pageX,c.y=a.pageY,b.setTooltipPosition(c)}function d(c,d,f){var g=function(a,b,c,d){return Math.sqrt((c-a)*(c-a)+(d-b)*(d-b))},h=function(a,b,c,d,e,f,h){if(!h||(h=function(a,b,c,d,e,f){if("undefined"!=typeof c)return{x:c,y:b};if("undefined"!=typeof d)return{x:a,y:d};var g,h=-1/((f-d)/(e-c));return{x:g=(e*(a*h-b+d)+c*(a*-h+b-f))/(h*(e-c)+d-f),y:h*g-h*a+b}}(a,b,c,d,e,f),h.x>=Math.min(c,e)&&h.x<=Math.max(c,e)&&h.y>=Math.min(d,f)&&h.y<=Math.max(d,f))){var i=d-f,j=e-c,k=c*f-d*e;return Math.abs(i*a+j*b+k)/Math.sqrt(i*i+j*j)}var l=g(a,b,c,d),m=g(a,b,e,f);return l>m?m:l};if(f)b.showTooltip(f,e.tooltipOptions.snap?f:d);else if(e.plotOptions.series.lines.show&&e.tooltipOptions.lines===!0){var i=e.plotOptions.grid.mouseActiveRadius,j={distance:i+1},k=d;a.each(b.getData(),function(a,c){for(var f=0,i=-1,l=1;l<c.data.length;l++)c.data[l-1][0]<=d.x&&c.data[l][0]>=d.x&&(f=l-1,i=l);if(-1===i)return void b.hideTooltip();var m={x:c.data[f][0],y:c.data[f][1]},n={x:c.data[i][0],y:c.data[i][1]},o=h(c.xaxis.p2c(d.x),c.yaxis.p2c(d.y),c.xaxis.p2c(m.x),c.yaxis.p2c(m.y),c.xaxis.p2c(n.x),c.yaxis.p2c(n.y),!1);if(o<j.distance){var p=g(m.x,m.y,d.x,d.y)<g(d.x,d.y,n.x,n.y)?f:i,q=(c.datapoints.pointsize,[d.x,m.y+(n.y-m.y)*((d.x-m.x)/(n.x-m.x))]),r={datapoint:q,dataIndex:p,series:c,seriesIndex:a};j={distance:o,item:r},e.tooltipOptions.snap&&(k={pageX:c.xaxis.p2c(q[0]),pageY:c.yaxis.p2c(q[1])})}}),j.distance<i+1?b.showTooltip(j.item,k):b.hideTooltip()}else b.hideTooltip()}var e=this,f=a.plot.plugins.length;if(this.plotPlugins=[],f)for(var g=0;f>g;g++)this.plotPlugins.push(a.plot.plugins[g].name);b.hooks.bindEvents.push(function(b,f){if(e.plotOptions=b.getOptions(),"boolean"==typeof e.plotOptions.tooltip&&(e.plotOptions.tooltipOpts.show=e.plotOptions.tooltip,e.plotOptions.tooltip=e.plotOptions.tooltipOpts,delete e.plotOptions.tooltipOpts),e.plotOptions.tooltip.show!==!1&&"undefined"!=typeof e.plotOptions.tooltip.show){e.tooltipOptions=e.plotOptions.tooltip,e.tooltipOptions.$compat?(e.wfunc="width",e.hfunc="height"):(e.wfunc="innerWidth",e.hfunc="innerHeight");e.getDomElement();a(b.getPlaceholder()).bind("plothover",d),a(f).bind("mousemove",c)}}),b.hooks.shutdown.push(function(b,e){a(b.getPlaceholder()).unbind("plothover",d),a(e).unbind("mousemove",c)}),b.setTooltipPosition=function(b){var c=e.getDomElement(),d=c.outerWidth()+e.tooltipOptions.shifts.x,f=c.outerHeight()+e.tooltipOptions.shifts.y;b.x-a(window).scrollLeft()>a(window)[e.wfunc]()-d&&(b.x-=d),b.y-a(window).scrollTop()>a(window)[e.hfunc]()-f&&(b.y-=f),isNaN(b.x)?e.tipPosition.x=e.tipPosition.xPrev:(e.tipPosition.x=b.x,e.tipPosition.xPrev=b.x),isNaN(b.y)?e.tipPosition.y=e.tipPosition.yPrev:(e.tipPosition.y=b.y,e.tipPosition.yPrev=b.y)},b.showTooltip=function(a,c,d){var f=e.getDomElement(),g=e.stringFormat(e.tooltipOptions.content,a);""!==g&&(f.html(g),b.setTooltipPosition({x:c.pageX,y:c.pageY}),f.css({left:e.tipPosition.x+e.tooltipOptions.shifts.x,top:e.tipPosition.y+e.tooltipOptions.shifts.y}).show(),"function"==typeof e.tooltipOptions.onHover&&e.tooltipOptions.onHover(a,f))},b.hideTooltip=function(){e.getDomElement().hide().html("")}},c.prototype.getDomElement=function(){var b=a("."+this.tooltipOptions.cssClass);return 0===b.length&&(b=a("<div />").addClass(this.tooltipOptions.cssClass),b.appendTo("body").hide().css({position:"absolute"}),this.tooltipOptions.defaultTheme&&b.css({background:"#fff","z-index":"1040",padding:"0.4em 0.6em","border-radius":"0.5em","font-size":"0.8em",border:"1px solid #111",display:"none","white-space":"nowrap"})),b},c.prototype.stringFormat=function(a,b){var c,d,e,f,g,h=/%p\.{0,1}(\d{0,})/,i=/%s/,j=/%c/,k=/%lx/,l=/%ly/,m=/%x\.{0,1}(\d{0,})/,n=/%y\.{0,1}(\d{0,})/,o="%x",p="%y",q="%ct",r="%n";if("undefined"!=typeof b.series.threshold?(c=b.datapoint[0],d=b.datapoint[1],e=b.datapoint[2]):"undefined"!=typeof b.series.curvedLines?(c=b.datapoint[0],d=b.datapoint[1]):"undefined"!=typeof b.series.lines&&b.series.lines.steps?(c=b.series.datapoints.points[2*b.dataIndex],d=b.series.datapoints.points[2*b.dataIndex+1],e=""):(c=b.series.data[b.dataIndex][0],d=b.series.data[b.dataIndex][1],e=b.series.data[b.dataIndex][2]),null===b.series.label&&b.series.originSeries&&(b.series.label=b.series.originSeries.label),"function"==typeof a&&(a=a(b.series.label,c,d,b)),"boolean"==typeof a&&!a)return"";if(e&&(a=a.replace(q,e)),"undefined"!=typeof b.series.percent?f=b.series.percent:"undefined"!=typeof b.series.percents&&(f=b.series.percents[b.dataIndex]),"number"==typeof f&&(a=this.adjustValPrecision(h,a,f)),b.series.hasOwnProperty("pie")&&(g=b.series.data[0][1]),"number"==typeof g&&(a=a.replace(r,g)),a="undefined"!=typeof b.series.label?a.replace(i,b.series.label):a.replace(i,""),a="undefined"!=typeof b.series.color?a.replace(j,b.series.color):a.replace(j,""),a=this.hasAxisLabel("xaxis",b)?a.replace(k,b.series.xaxis.options.axisLabel):a.replace(k,""),a=this.hasAxisLabel("yaxis",b)?a.replace(l,b.series.yaxis.options.axisLabel):a.replace(l,""),this.isTimeMode("xaxis",b)&&this.isXDateFormat(b)&&(a=a.replace(m,this.timestampToDate(c,this.tooltipOptions.xDateFormat,b.series.xaxis.options))),this.isTimeMode("yaxis",b)&&this.isYDateFormat(b)&&(a=a.replace(n,this.timestampToDate(d,this.tooltipOptions.yDateFormat,b.series.yaxis.options))),"number"==typeof c&&(a=this.adjustValPrecision(m,a,c)),"number"==typeof d&&(a=this.adjustValPrecision(n,a,d)),"undefined"!=typeof b.series.xaxis.ticks){var s;s=this.hasRotatedXAxisTicks(b)?"rotatedTicks":"ticks";var t=b.dataIndex+b.seriesIndex;for(var u in b.series.xaxis[s])if(b.series.xaxis[s].hasOwnProperty(t)&&!this.isTimeMode("xaxis",b)){var v=this.isCategoriesMode("xaxis",b)?b.series.xaxis[s][t].label:b.series.xaxis[s][t].v;v===c&&(a=a.replace(m,b.series.xaxis[s][t].label))}}if("undefined"!=typeof b.series.yaxis.ticks)for(var w in b.series.yaxis.ticks)if(b.series.yaxis.ticks.hasOwnProperty(w)){var x=this.isCategoriesMode("yaxis",b)?b.series.yaxis.ticks[w].label:b.series.yaxis.ticks[w].v;x===d&&(a=a.replace(n,b.series.yaxis.ticks[w].label))}return"undefined"!=typeof b.series.xaxis.tickFormatter&&(a=a.replace(o,b.series.xaxis.tickFormatter(c,b.series.xaxis).replace(/\$/g,"$$"))),"undefined"!=typeof b.series.yaxis.tickFormatter&&(a=a.replace(p,b.series.yaxis.tickFormatter(d,b.series.yaxis).replace(/\$/g,"$$"))),a},c.prototype.isTimeMode=function(a,b){return"undefined"!=typeof b.series[a].options.mode&&"time"===b.series[a].options.mode},c.prototype.isXDateFormat=function(a){return"undefined"!=typeof this.tooltipOptions.xDateFormat&&null!==this.tooltipOptions.xDateFormat},c.prototype.isYDateFormat=function(a){return"undefined"!=typeof this.tooltipOptions.yDateFormat&&null!==this.tooltipOptions.yDateFormat},c.prototype.isCategoriesMode=function(a,b){return"undefined"!=typeof b.series[a].options.mode&&"categories"===b.series[a].options.mode},c.prototype.timestampToDate=function(b,c,d){var e=a.plot.dateGenerator(b,d);return a.plot.formatDate(e,c,this.tooltipOptions.monthNames,this.tooltipOptions.dayNames)},c.prototype.adjustValPrecision=function(a,b,c){var d,e=b.match(a);return null!==e&&""!==RegExp.$1&&(d=RegExp.$1,c=c.toFixed(d),b=b.replace(a,c)),b},c.prototype.hasAxisLabel=function(b,c){return-1!==a.inArray(this.plotPlugins,"axisLabels")&&"undefined"!=typeof c.series[b].options.axisLabel&&c.series[b].options.axisLabel.length>0},c.prototype.hasRotatedXAxisTicks=function(b){return-1!==a.inArray(this.plotPlugins,"tickRotor")&&"undefined"!=typeof b.series.xaxis.rotatedTicks};var d=function(a){new c(a)};a.plot.plugins.push({init:d,options:b,name:"tooltip",version:"0.8.5"})}(jQuery);