function chk(str)
{
	var pass = true;
	var ch;
	var stralarm = new Array("<",">");
	for (var i=0;i<stralarm.length;i++){ //依序載入使用者輸入的每個字元
		for (var j=0;j<str.length;j++){
			ch=str.substr(j,1);
			if (ch==stralarm[i]){
				//如果包含禁止字元
				pass = false; //設置此變數為true
			}
		} 
	}
	return pass;
}