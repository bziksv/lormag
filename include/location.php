<? if(!$arCityes = include $_SERVER['DOCUMENT_ROOT'].'/cityes.php') return false; ?>

<div class="top-panel__geo-location-block" id="location_btn">
 <i class="icon-map-marker"></i>
	<?=($_COOKIE['city']) ?: 'Город'?>
</div>
<div class="mfeedback-p" id="location" style="max-width: 500px;">
 <span class="button b-close">×</span>
	<div class="mfeedback-p-head">
		 Город
	</div>
	 <? if($arCityes['show'] && $arCityes['hide']): ?>
	<div class="city">
		 <? if($arCityes['show']): ?>
		<div class="item-city">
			 <? foreach ($arCityes['show'] as $s):?> <a href="javascript:void(0)" class="c"><?=$s?></a>
			<? endforeach; ?>
		</div>
		 <? endif; ?>
		<div class="item-city">
 <a href="javascript:void(0)" class="city-show" onclick="$(this).closest('.city').find('.item-city:last-child').toggleClass('active'); return false;">Показать</a>
		</div>
		 <? if($arCityes['hide']): ?>
		<div class="item-city">
			 <? foreach ($arCityes['hide'] as $h):?> <a href="javascript:void(0)" class="c"><?=$h?></a>
			<? endforeach; ?>
		</div>
		 <? endif; ?>
	</div>
	 <? endif; ?>
</div>

<script>
    $('#location_btn').click(function(){
        $('#location').bPopup({
            zIndex:1000,
            position: ['auto', 50]
        });
    });

    $('.city .item-city a.c').click(function(){
        var city = $(this).text();
        document.cookie = "city=" + city + "; path=/;";
		$('#location_btn').html('<i class="icon-map-marker"></i>' + '<span>' + city + '</span>');

        var bPopup = $('#location').bPopup();
        bPopup.close();
    });
</script>