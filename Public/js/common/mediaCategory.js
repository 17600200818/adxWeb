function mediaCategory(isDefault) {
        //媒体分类
        $.ajax({
            type: "POST",
            url: "/Common/get_category",
            dataType: "json",
            success: function (data) {
                if (isDefault == 'yes') {
                    $("#msubCategory").append("<option value='' selected>全部分类</option>");
                }
                $.each(data.data, function (i, item) {
                	
                    if (isDefault == 'yes') {
                        $("#mcategory").append("<option value='" + item.c1 + "'>" + item.n1 + "</option>");
                    } else {
                        if (i == 0) {
                            $("#mcategory").append("<option value='" + item.c1 + "' selected>" + item.n1 + "</option>");

                        } else {
                            $("#mcategory").append("<option value='" + item.c1 + "'>" + item.n1 + "</option>");
                        }
                    }
                });
//                $("select[name=category]").selectList();
                if ($("#dataList").val() == 'getMediaList') {
                    $.fn.getMediaList();
                }
                var c1 = $("#mcategory").val();
                if (c1 != '' && c1 != '全部分类') {
                    $.ajax({
                        type: "POST",
                        url: "/Common/get_category",
                        data: {"c1": c1},
                        dataType: "json",
                        success: function (data) {
                            $("#msubCategory").empty();
                            if (isDefault == 'yes') {
                                $("#msubCategory").append("<option value=''>全部分类</option>");
                            }
                            $.each(data.data, function (i, item) {
                                $("#msubCategory").append("<option value='" + item.c2 + "'>" + item.n2 + "</option>");
                            });
//                            $("select[name=subCategory]").selectList();
                        }
                    });
                } else {
                    if (isDefault == 'yes') {
                        $("#msubCategory").empty();
                        $("#msubCategory").append("<option value=''>全部分类</option>");
//                        $("select[name=subCategory]").selectList();
                    }
                }
            }
        });
        //媒体二级分类
        $("#mcategory").change(function () {
            var c1 = $("#mcategory").val();
            if (c1 != '' && c1 != '全部分类') {
                $.ajax({
                    type: "POST",
                    url: "/Common/get_category",
                    data: {"c1": c1},
                    dataType: "json",
                    success: function (data) {
                        $("#msubCategory").empty();
                        if (isDefault == 'yes') {
                            $("#msubCategory").append("<option value='' selected>全部分类</option>");
                        }
                        $.each(data.data, function (i, item) {
                            $("#msubCategory").append("<option value='" + item.c2 + "'>" + item.n2 + "</option>");

                        });
//                        $("select[name=subCategory]").selectList();

                    }
                });
            } else {
                $("#msubCategory").empty();
                $("#msubCategory").append("<option value='' selected>全部分类</option>");
//                $("select[name=subCategory]").selectList();
                if ($("#dataList").val() == 'getMediaList') {
                    $.fn.getMediaList();
                }

            }
        });
    }