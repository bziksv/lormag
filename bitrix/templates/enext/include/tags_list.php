<?php
if (empty($arParams["SECTION"]["UF_META_TAGS"])) {
    return;
}

use Bitrix\Main\Page\Asset;

$asset = Asset::getInstance();
$asset->addString('<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>', true);
$asset->addString(<<<'HTML'
<style>

    .tags-list{
        margin-bottom: 30px;
    }
    .sub-links-2 {
        font-size: 14px;
        font-weight: 500;
        padding: 0;
        margin-right: 120px !important;
        list-style: none;
        padding-left: 20px;
        padding-right: 20px;
        width: auto;
        position: relative;
        overflow: hidden;
        display: block !important;
    }

    .sub-links-2.open,
    .sub-links-2.opens {
        height: auto;
        margin: 0 auto 30px auto !important;
    }

    .sub-links-2 li {
        margin: 5px;
        display: inline-block;
    }

    .sub-links-2 a {
        color: #66b3d4;
        border: #66b3d4 solid 1px;
        padding: 4px 20px;
        border-radius: 5px;
        display: inline-block;
        text-decoration: none;
    }

    .sub-links-2 a:hover {
        text-decoration: none;
        color: #ffffff;
        border: #66b3d4 solid 1px;
        background: #66b3d4;
    }

    .sub-links-2 .slick-arrow {
        margin: auto;
    }


    .sub-links-2 .slick-arrow:before {
        color: #66b3d4;
        font-size: 26px;
        vertical-align: middle;
    }

    .subcategories {
        display: -webkit-box;
        display: -webkit-flex;
        display: -moz-box;
        display: -ms-flexbox;
        display: flex;
        position: relative;
    }

    .subcategories .navi {
        position: absolute;
        bottom: 0;
        right: 0;
    }

    .subcategories .navi span {
        cursor: pointer;
        color: #66b3d4;
        text-decoration: dotted underline;
        font-size: 14px;
    }

    .subcategories .navi span:hover {
        text-decoration: none;
    }
    .sub-links-2 .slick-next:after {
        display: inline-block;
        content: '';
        width: 30px;
        height: 39px;
        background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAgCAMAAAAynjhNAAAAaVBMVEX///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////9tAe1xAAAAI3RSTlP9B/Xv6OHZvqmfinVqYFVLQjgmFxAf0Mcws5SAf5W0xi/PHr7jVrUAAABxSURBVCjP3dJZCoAgEIBhbbN9TyvTrPsfMgxDTPQAPn84/gMDIJynZVyHvmubuqIlK/INkStLkzgCQLFQfEjmiGAX7/L1afAth2u2hluMnexLo5K5/bde7OUAy5kvjX5poZXr4f5LDaP8l2az0Gwc8gMuITF/U6PCDgAAAABJRU5ErkJggg==') no-repeat;
        position: absolute;
        top: -15px;
        left: -27px;
    }

    .sub-links-2 .slick-prev:after {
        content: '';
        display: inline-block;
        width: 30px;
        height: 39px;
        background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAgCAMAAAAynjhNAAAAaVBMVEX///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////9tAe1xAAAAI3RSTlP9DfXo4dnQx7Spn4p1amBVS0I4Jhcfve4wBZSAf5XvLwS+HhGGx64AAAB2SURBVCjP3ZJHDoAwEAND7733hP8/kioZEpC4hvPI9qy0hBC10nTDtErbcQev84MwipO0zopcUZoNjxfcn3jiMVtxy2GKcuCjfD7S2AbGtpgW1ZDueDXxMGxTuc0v+N0c5dimcpuPXz6V3cv/Y86e1N7wtONmATKLMZCWB9C4AAAAAElFTkSuQmCC') no-repeat;
        position: absolute;
        top: -15px;
        right: -27px;
    }

    .slick-prev,
    .slick-next {
        width: auto;
        height: auto;
        z-index: 1;
    }

    .slick-next {
        right: 2px;
    }

    .slick-prev {
        left: 2px;
    }

    /* Arrows */

    .slick-prev,
    .slick-next {
        position: absolute;
        display: block;
        height: 30px;
        width: 20px;
        line-height: 0px;
        font-size: 0px;
        cursor: pointer;
        background: transparent;
        color: transparent;
        top: 90%;
        -webkit-transform: translate(0, -50%);
        -ms-transform: translate(0, -50%);
        -moz-transform: translate(0, -50%);
        -o-transform: translate(0, -50%);
        transform: translate(0, -50%);
        padding: 0;
        border: none;
        outline: none;
    }

    .slick-prev:hover,
    .slick-prev:focus,
    .slick-next:hover,
    .slick-next:focus {
        outline: none;
        background: transparent;
        color: transparent;
    }

    .slick-prev:hover:before,
    .slick-prev:focus:before,
    .slick-next:hover:before,
    .slick-next:focus:before {
        opacity: 1;
    }

    .slick-prev.slick-disabled:before,
    .slick-next.slick-disabled:before {
        opacity: 0.25;
    }

</style>
HTML
, true);
?>

<?
$ids = $arParams["SECTION"]["UF_META_TAGS"];

$arSelect = Array("ID", "IBLOCK_ID", "NAME","PROPERTY_*");
$arFilter = Array("IBLOCK_ID" => 36, "ID" => $ids, "ACTIVE"=>"Y");
$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
while($ob = $res->GetNextElement()):
$arProps = $ob->GetProperties();
?>

<div class="tags-list">

	<div class="h2"><?=$arProps['TITLE']['VALUE']?></div>

    <div class="subcategories">
        <ul class="tag-slider sub-links-2">
            <? foreach ($arProps['LIST']['VALUE'] as $key => $tag): ?>
            	<li><a href="<?=$arProps['LIST']['DESCRIPTION'][$key]?>"><?=$tag?></a></li>
            <? endforeach; ?>
        </ul>
        <div class="navi">
            <span class="open">Показать все</span>
            <span class="close" hidden>Свернуть</span>
        </div>
    </div>

</div>

<? endwhile; ?>

<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

<script>

    $('.tag-slider').slick({
        dots: false,
        arrows: true,
        infinite: true,
        autoplay: true,
        variableWidth: true,
        centerMode: true,
        slidesToShow: 3,
 		prevArrow: '<i class="icon-arrow-left slick-prev"></i>',
		nextArrow: '<i class="icon-arrow-right slick-next"></i>',
        responsive: [
            {
                breakpoint: 767,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
        ]
    });

    $(document).ready(function() {
        $(".subcategories .open").click(function(){
			let tags = $(this).closest('.tags-list');
            $(this).hide();
            $(".subcategories .close", tags).show();
            $(".subcategories .sub-links-2", tags).addClass("open");
            $('.tag-slider', tags).slick('unslick');
        });
        $(".subcategories .close").click(function(){
			let tags = $(this).closest('.tags-list');
            $(this).hide();
            $(".subcategories .open", tags).show();
            $(".subcategories .sub-links-2", tags).removeClass("open");
            $('.tag-slider'. tags).slick({
                dots: false,
                arrows: true,
                infinite: true,
                autoplay: true,
                variableWidth: true,
                centerMode: true,
                slidesToShow: 3,
            });
        });
    });

</script>
