(function($){
	$.fn.pageHtml = function($nowPage, $totalPages, pageNumId, htmlId, pageId, callBackType) {



		if ($totalPages > 1) {

			var $pageNum = 6;

			var $now_cool_page = $pageNum / 2;

			var $now_cool_page_ceil = Math.ceil($now_cool_page);

			var firstPage = '<a class="nextpage fa fa-step-backward firstPage" ></a>';

			var endPage = '<a class="prevpage fa fa-step-forward endPage"></a>';

			//var goPage = '<a class="inputpage"><input class="printpage" id="' + pageNumId + '" type="text"></a> <a class="gopage" id="goPage">GO</a>';

			var $i;

			var $page;

			var $link_page = '';



			for ($i = 1; $i <= $pageNum; $i++) { //这个if就是为了判断页数的位置

				//如果当前页减去（要显示的一半）小于0的话，就让它显示在中间

				if ((parseInt($nowPage) - parseInt($now_cool_page)) <= 0) {

					$page = $i;



				} else if ((parseInt($nowPage) + parseInt($now_cool_page) - 1) >= $totalPages) {

					$page = $totalPages - $pageNum + $i;



				} else {



					$page = $nowPage - $now_cool_page_ceil + $i;

				}



				if ($page > 0 && $page != parseInt($nowPage)) {



					if ($page <= $totalPages) {

						$link_page += '<span class="nextPage">' + $page + '</span>';

					} else {

						break;

					}

				} else {



					if ($page > 0 && $totalPages != 1) {

						$link_page += '<span class="current">' + $page + '</span>';

					}

				}



			}

			//             开始页        123456      结束页     go跳转

			var pagehtml = firstPage + $link_page + endPage;

			$("#" + htmlId).html(pagehtml);

			$.fn.goPage(pageNumId, pageId, $totalPages, callBackType);

			$.fn.nextPage(pageId, callBackType);

			$.fn.firstPage(pageId, callBackType);

			$.fn.endPage($totalPages, pageId, callBackType);

		} else {

			//数据不够就显示一页的语句

			pagehtml = '<a class="nextpage fa fa-step-backward firstPage"></a>';

			pagehtml += '<span class="current">1</span><a class="prevpage fa fa-step-forward endPage"></a>';

			$("#" + htmlId).html(pagehtml);

		}

	}

	$.fn.goPage = function(pageNumId, pageId, $totalPages, callBackType) {



		$("#goPage").bind('click', function() {

			var numReg = /^[0-9]{1,9}?$/;

			var page = $("#" + pageNumId).val();

			if (numReg.test(page)) {

				if (page <= $totalPages) {

					$("#" + pageId).val(page);

					$.fn.pageList(callBackType);

				}



			}



		})



	}

	$.fn.nextPage = function(pageId, callBackType) {

		$(".nextPage").bind('click', function() {

			$("#" + pageId).val($(this).text());

			$.fn.pageList(callBackType);

		})

	}

	$.fn.firstPage = function(pageId, callBackType) {

		$(".firstPage").bind('click', function() {

			$("#" + pageId).val(1);

			$.fn.pageList(callBackType);

		})

	}

	$.fn.endPage = function(maxPage, pageId, callBackType) {

		$(".endPage").bind('click', function() {

			$("#" + pageId).val(maxPage);

			$.fn.pageList(callBackType);

		})

	}

	$.fn.pageList = function(callBackType) {
		switch (callBackType) {
			case 'creativeList':
				getcretaivelist(1);
				break;
		}

	}
})(jQuery);