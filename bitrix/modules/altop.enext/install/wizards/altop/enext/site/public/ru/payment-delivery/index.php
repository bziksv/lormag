<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Оплата и доставка");?>

<p>Сайт является демонстрационным, все данные опубликованы для примера и не являются публичной офертой. Вы можете разместить здесь любую информацию о стоимости ваших услуг. Создавайте любое число таблиц с нужной структурой. Копируйте стили данных таблиц, чтобы сделать свои по аналогии.</p>
<h2>Оплата</h2>
<table class="table table-bordered table-hover">
	<thead>
		<tr>
			<th>№</th>
			<th>Вид оплаты</th>
			<th>Инструкция по оплате</th>
		</tr>
	</thead>
	<tbody>
	<tr>
		<td colspan="3">Наличный и безналичный расчет</td>
	</tr>
	<tr>
		<td>1</td>
		<td>Наличный расчет при получении товара</td>
		<td>-</td>
	</tr>
	<tr>
		<td>2</td>
		<td>Безналичный расчет для организаций. 100% Предоплата</td>
		<td>-</td>
	</tr>
	<tr>
		<td colspan="3">Онлайн оплата на сайте</td>
	</tr>
	<tr>
		<td>1</td>
		<td>Банковские карты МИР, VISA, Mastercard</td>
		<td>-</td>
	</tr>
	<tr>
		<td>2</td>
		<td>Яндекс.Деньги</td>
		<td>-</td>
	</tr>
	<tr>
		<td>3</td>
		<td>Webmoney</td>
		<td>-</td>
	</tr>
	<tr>
		<td>4</td>
		<td>QIWI</td>
		<td>-</td>
	</tr>
	<tr>
		<td>5</td>
		<td>Сбербанк Онлайн</td>
		<td>-</td>
	</tr>
	</tbody>
</table>
<h2>Стоимость доставки</h2>
<table class="table table-striped">
	<thead>
	<tr>
		<th>№</th>
		<th>Доставка</th>
		<th>Стоимость доставки</th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td colspan="3">Доставка по Москве</td>
	</tr>
	<tr>
		<td>1</td>
		<td>В пределах МКАД</td>
		<td>Бесплатно</td>
	</tr>
	<tr>
		<td>2</td>
		<td>До 30 км от МКАД</td>
		<td>500 руб.</td>
	</tr>
	<tr>
		<td>3</td>
		<td>30 - 100 км от МКАД</td>
		<td>20 руб. за км</td>
	</tr>
	<tr>
		<td colspan="3">Доставка по России</td>
	</tr>
	<tr>
		<td>1</td>
		<td>Почта России</td>
		<td>По тарифам компании</td>
	</tr>
	<tr>
		<td>2</td>
		<td>Транспортная компания 1</td>
		<td>По тарифам компании</td>
	</tr>
	<tr>
		<td>3</td>
		<td>Транспортная компания 2</td>
		<td>По тарифам компании</td>
	</tr>
	</tbody>
</table>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>