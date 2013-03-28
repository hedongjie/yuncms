function confirmurl(url,message) {
	if(confirm(message)) redirect(url);
}
function redirect(url) {
	location.href = url;
}
//滚动条
$(function(){
	$(":text").addClass('input-text');
})

/**
 * 全选checkbox,注意：标识checkbox id固定为为check_box
 * @param string name 列表check名称,如 uid[]
 */
function selectall(name) {
	if ($("#check_box").attr("checked")==false) {
		$("input[name='"+name+"']").each(function() {
			this.checked=false;
		});
	} else {
		$("input[name='"+name+"']").each(function() {
			this.checked=true;
		});
	}
}
function openwinx(url,name,w,h) {
	if(!w) w=screen.width-4;
	if(!h) h=screen.height-95;
    window.open(url,name,"top=100,left=400,width=" + w + ",height=" + h + ",toolbar=no,menubar=no,scrollbars=yes,resizable=yes,location=no,status=no");
}
//弹出对话框
function omnipotent(id,linkurl,title,close_type,w,h) {
	if(!w) w='700px';
	if(!h) h='500px';
	art.dialog.open(linkurl,{
		id:id,
		title:title, 
		width:w, 
		height:h, 
		lock:true,
		ok: function(iframeWin, topWin){
			if(close_type !=1) {
				var form = iframeWin.document.getElementById('dosubmit');
				form.click();
				return false;
			}
		},
		cancel: function(){}
	});
	void(0);
}