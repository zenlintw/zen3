<?php
	// 討論板與精華區各項排序依據
	$OrderBy = Array(
		'board'=>array(
				'node'		=> 'node,pt,site',
				'pt'		=> 'pt,node,site',
				'subject'	=> 'subject,pt,site',
				'poster'	=> 'poster,pt,node,site',
				'rank'		=> 'rank DESC,pt,node,site',
				'hit'		=> 'hit DESC,pt,node,site',
				'node_r'	=> 'node DESC,pt DESC,site DESC',
				'pt_r'		=> 'pt DESC,node DESC,site DESC',
				'subject_r'	=> 'subject DESC,pt DESC,site DESC',
				'poster_r'	=> 'poster DESC,pt DESC,node DESC,site DESC',
				'rank_r'	=> 'rank,pt DESC,node DESC,site DESC',
				'hit_r'		=> 'hit,pt DESC,node DESC,site DESC',
				   ),
		'quint'=>array(
				'node'		=> 'type,node,pt,site',
				'pt'		=> 'type,pt,node,site',
				'subject'	=> 'type,subject,pt,site',
				'poster'	=> 'type,poster,pt,node,site',
				'rank'		=> 'type,rank DESC,pt,node,site',
				'hit'		=> 'type,hit DESC,pt,node,site',
				'node_r'	=> 'type,node DESC,pt DESC,site DESC',
				'pt_r'		=> 'type,pt DESC,node DESC,site DESC',
				'subject_r'	=> 'type,subject DESC,pt DESC,site DESC',
				'poster_r'	=> 'type,poster DESC,pt DESC,node DESC,site DESC',
				'rank_r'	=> 'type,rank,pt DESC,node DESC,site DESC',
				'hit_r'		=> 'type,hit,pt DESC,node DESC,site DESC',
				   )
		);

	$OrderDirection = array(
				'node'		=> 'up',
				'pt'		=> 'up',
				'subject'	=> 'up',
				'poster'	=> 'up',
				'rank'		=> 'down',
				'hit'		=> 'down',
				'node_r'	=> 'down',
				'pt_r'		=> 'down',
				'subject_r'	=> 'down',
				'poster_r'	=> 'down',
				'rank_r'	=> 'up',
				'hit_r'		=> 'up'
				);

	/**************
	 * Javascript 的 order by 陣列順序
	 **************/
	$js_OrderBy = Array(
				'node'		=>0,
				'pt'		=>1,
				'subject'	=>2,
				'poster'	=>3,
				'hit'		=>4,
				'rank'		=>5,
				'node_r'	=>6,
				'pt_r'		=>7,
				'subject_r'	=>8,
				'poster_r'	=>9,
				'hit_r'		=>10,
				'rank_r'	=>11
			  );

	/**
	 * generate_order_link()
	 *    產生排序連結
	 *    @pram $position : 位置字串( subject, poster, ... )
	 *    @pram $title 	  : 該位置語系標題 ( $MSG[] )
	 *    @pram $sortby	  : 一般討論版放 $sysSession->sortby, 精華區放 $sysSession->q_sortby
	 *    @return 		  : 產生之連結語法
	 **/
	function generate_order_link($position, $title, $sortby) {
		global $sysSession, $icon_dir, $js_OrderBy, $MSG, $OrderDirection;
		if( substr($sortby, -2) === '_r') {
			$sort_pos = substr($sortby, 0, -2);
			$js_sort  = 'sortBy(' . $js_OrderBy[$sort_pos] .');';
		} else {
			$sort_pos = $sortby;
			$js_sort  = 'sortBy(' . $js_OrderBy[$sort_pos.'_r'] .');';
		}

		if($sort_pos==$position) {
			$link = '<a href="javascript:;" onclick="' . $js_sort .
				' return false;" class="cssAnchor" title="'.$MSG['order'][$sysSession->lang].'">';
			return '<b>'.$link . $title.
				sprintf('<img src="/theme/%s/learn/dude07232001%s.gif" border="0" align="absmiddle">',
				        $sysSession->theme,
						$OrderDirection[$sortby]).
				'</a></b>';
		} else {
			$link = '<a href="javascript:;" onclick="sortBy(' . $js_OrderBy[$position] .');' .
				' return false;" class="cssAnchor" title="'.$MSG['order'][$sysSession->lang].'">';
			return $link.$title .'</a>';
		}
	}

	/**************************************
	 * 針對回覆文章作縮排(精華區無回覆文章)
	 **************************************/
	function indent($node, $sortby){
		global $sysSession;
		if(empty($sortby)) $sortby = $sysSession->sortby;
		if ($sortby != 'node') return '';
		$nl = (strlen($node)-9)/9;
		return str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $nl);
	}
?>
