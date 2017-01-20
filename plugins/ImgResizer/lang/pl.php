<?php
$i18n = array(
    'CONFIGURE' => 'Konfiguracja Image Resizer',
    'RESOLUTIONS' => 'Ustawienia rozdzielczości',
    'OTHER_SETTINGS' => 'Ustawienia obrazków',
    'VALIDATION_ERROR' => 'Wypełnij prawidłowo pola oznaczone kolorem czerwonym.',
    'UPDATED' => 'Ustawienia zapisane.',
    'BY_WIDTH' => 'Dozwolone szerokości (oddzielone przecinkami):',
    'BY_HEIGHT' => 'Dozwolone wysokości (oddzielone przecinkami):',
	'BY_FILL' => 'Dozwolone wymiary dla dopasowania "fill" (obcinanie boków) {szerokość}x{wysokość} np. 200x100 (oddzielone przecinkami):',
    'BY_FIT' => 'Dozwolone wymiary dla dopasowania "fit" {szerokość}x{wysokość} np. 200x100 (oddzielone przecinkami):',
    'SHARPEN' => 'Wyostrz zmniejszone obrazy:',
    'QUALITY' => 'Jakość (0 - 100):',
    'CACHE_COUNT' => 'Ilość obrazków w cache:',
    'CLEAR_CACHE' => 'Wyczyść cache',
    'LIBRARY' => 'Używane rozszerzenie PHP (GD = gorsza jakość, ImageMagick = lepsza jakość):',
    'SAVE' => 'Zapisz miany',
    'WIDTH' => 'szerokość',
    'HEIGHT' => 'wysokość',
    
    'HELP' => '<p>W pliku szablonu użyj funkcji <code>image_resizer_src($mode, $resolution, $img)</code> aby wygenerować url zmniejszonego obrazka.<br/>
	Jeżeli któryś z boków obrazka źródłowego jest mniejszy niż wymagany, wtedy zwrócony zostanie oryginalny obrazek.</p>
    <p>Parametry funkcji:</p>
    <ul>
        <li><b>mode</b> - dozwolone wartości to "height", "width", "fill" i "fit", wskazuje jak zmniejszyć obrazek</li>
        <li><b>resolution</b> - jedna ze zdefiniowanych wartości w konfiguracji</li>
        <li><b>img</b>- ścieżka do obrazka z katalogu <code>data/</code> GetSimple, w jednym z formatów:<br>
            <code>/data/uploads/img.jpg</code> (relatywna do folderu głównego domeny)<br>
            <code>http://example.com/data/uploads/img.jpg</code>
        </li>
    </ul>'
);