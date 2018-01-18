function login(){
	var email = $("#email").val();
    var password = $("#password").val();//$.md5();
    var imgCode = $("#imgCode").val();
    if (email == '' || password == '' ) {
        $(".mes").html("用户名密码不能为空");
        return true ;
    }
//    password = $.md5(password);
     if(imgCode == ''){
//        layer.msg('验证码不能为空 ',{icon:5});
//        return true;
    }
     
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: "/",
        data: {email: email, password: password, vcode: imgCode},
        success: function(msg) {
            if (msg.status == '2') {
                window.location.href = msg.url;
            } else {
                imgsrc = $("#img_path").attr('src');
                $("#img_path").attr('src', imgsrc + "?rand=" + Math.random());
                layer.msg(msg.errorInfo,{icon:5});

            }
        }
    });
}

