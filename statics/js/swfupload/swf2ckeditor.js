function flashupload(uploadid, name, textareaid, funcName, args, application, catid, authkey) {
	var args = args ? '&args='+args : '';
	var setting = '&application='+application+'&catid='+catid+'&authkey='+authkey;
	window.top.art.dialog.open('index.php?app=attachment&controller=attachments&action=swfupload'+args+setting,{
		title:name,
		id:uploadid,
		width:'500px',
		height:'420px',
		ok: function(iframeWin, topWin){
			if(funcName) {
				funcName.apply(this,[iframeWin,textareaid]);
			}else {
				submit_ckeditor(iframeWin,textareaid);
			}
		},
	    cancel: function(){}
		}
	);
}
function submit_ckeditor(iframeWin,textareaid){
	var in_content = iframeWin.$("#att-status").html();
	var del_content = iframeWin.$("#att-status-del").html();
	insert2editor(textareaid,in_content,del_content)
}

function submit_images(iframeWin,returnid){
	var in_content = iframeWin.$("#att-status").html().substring(1);
	var in_content = in_content.split('|');
	IsImg(in_content[0]) ? $('#'+returnid).attr("value",in_content[0]) : alert('选择的类型必须为图片类型');
}

function submit_attachment(iframeWin,returnid){
	var in_content = iframeWin.$("#att-status").html().substring(1);
	var in_content = in_content.split('|');
	$('#'+returnid).attr("value",in_content[0]);
}

function submit_files(iframeWin,returnid){
	var in_content = iframeWin.$("#att-status").html().substring(1);
	var in_content = in_content.split('|');
	var new_filepath = in_content[0].replace(uploadurl,'/');
	$('#'+returnid).attr("value",new_filepath);
}

function insert2editor(id,in_content,del_content) {	
	if(in_content == '') {return false;}
	var data = in_content.substring(1).split('|');
	var img = '';
	for (var n in data) {
		img += IsImg(data[n]) ? '<img src="'+data[n]+'" /><br />' : (IsSwf(data[n]) ? '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0"><param name="quality" value="high" /><param name="movie" value="'+data[n]+'" /><embed pluginspage="http://www.macromedia.com/go/getflashplayer" quality="high" src="'+data[n]+'" type="application/x-shockwave-flash" width="460"></embed></object>' :'<a href="'+data[n]+'" />'+data[n]+'</a><br />') ;
	}
	$.get("index.php?app=attachment&controller=attachments&action=swfdelete",{data: del_content},function(data){});
	CKEDITOR.instances[id].insertHtml(img);
}

function IsImg(url){
  var sTemp;
  var b=false;
  var opt="jpg|gif|png|bmp|jpeg";
  var s=opt.toUpperCase().split("|");
  for (var i=0;i<s.length ;i++ ){
    sTemp=url.substr(url.length-s[i].length-1);
    sTemp=sTemp.toUpperCase();
    s[i]="."+s[i];
    if (s[i]==sTemp){
      b=true;
      break;
    }
  }
  return b;
}

function IsSwf(url){
	  var sTemp;
	  var b=false;
	  var opt="swf";
	  var s=opt.toUpperCase().split("|");
	  for (var i=0;i<s.length ;i++ ){
	    sTemp=url.substr(url.length-s[i].length-1);
	    sTemp=sTemp.toUpperCase();
	    s[i]="."+s[i];
	    if (s[i]==sTemp){
	      b=true;
	      break;
	    }
	  }
	  return b;
	}