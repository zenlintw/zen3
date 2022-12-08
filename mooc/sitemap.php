<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    
    if ($profile['isPhoneDevice']) {
        $smarty->assign('Carousel', 'myCarouselPhone');	
    } else {
    	$smarty->assign('Carousel', 'myCarousel');	
    }
	
	$smarty->display('sitemap.tpl');
