<?php
$conn = new mysqli("localhost", "root", "", "szszsz");
if($conn->connect_error) echo "Nie można połączyć się z bazą: ".$conn->connect_error;
$conn->set_charset("utf8");
?>
<script type="text/babel">

var piesni = [];
var okazja = 0;

<?php

/* dodanie sezonowych okazji do puli wszystkich pieśni */

switch($_GET['a_formula']){
  case "Adwent": $inneokazje = ", 5"; break;
  case "Boże Narodzenie": $inneokazje = ", 6"; break;
  case "Wielki Post": $inneokazje = ", 7"; break;
  case "zwykła wielkanocna":
    case "Wielkanoc": $inneokazje = ", 8"; break;
  default: $inneokazje = "";
}

if($inneokazje != ""){ ?>
okazja = <?php echo substr($inneokazje, -1); ?>;
<?php }

/* kwerenda wszystkich pieśni */

$q = "SELECT * 
      FROM pieśni p 
      WHERE p.klasa IN (1, 2, 3, 4$inneokazje)";
$r = $conn->query($q) or die($q.$conn->error);
while($a = $r->fetch_assoc()){?>

piesni["<?php echo $a['tytuł']; ?>"] = new Array('klasa', 'katsiedlecki', 'nr', 'tonacja', 'naco', 'tekst');
<?php 
foreach($a as $name => $value){
	if(!in_array($name, ['tytuł', 'id'])){ ?>
piesni["<?php echo $a['tytuł']; ?>"]['<?php echo $name; ?>'] = `<?php echo $a[$name]; ?>`;

<?php
}}}
$r->free_result();
?>

/* zmienne window (globalne) */

var a_identyfikator = "<?php echo $_GET['a_identyfikator']; ?>"
var a_formula = "<?php echo $_GET['a_formula']; ?>";
const czst_color = "<?php echo $_GET['a_czescistale']; ?>";
var preferencje_names = ["Wejście", "Przygotowanie darów", "Komunia", "Uwielbienie", "Zakończenie"];

const ColorContext = React.createContext();

/* pieśni w tym secie */

var songlist = new Array();
<?php
/* lista pieśni */
$songlist = [
  "Przed mszą" => $_GET["a_pre"],
  "Majowe/Czerwcowe" => (in_array($_GET["a_formula"], ["majowe", "czerwcowe"])) ? $_GET["a_formula"] : "",
  "Gorzkie żale" => ($_GET["a_formula"] == "Wielki Post") ? "czst" : "",
  "Wejście" => $_GET["a_piesn_wejscie"],
  "Kyrie" => "czst",
  "Przed Gloria" => $_GET["a_piesn_przedgloria"],
  "Gloria" => (in_array($_GET["a_formula"], ["Adwent", "Wielki Post"])) ? "" : "czst",
  "Psalm" => $_GET["a_psalm"],
  "Sekwencja wielkanocna" => (in_array($_GET["a_formula"], ["Wielkanoc", "zwykła wielkanocna"])) ? "Niech w święto radosne" : "",
  "Aklamacja" => $_GET["a_aklamacja"],
  "Credo" => ($_GET['a_formula'] == "ślubna") ? "" : "czst",
  "Ślub" => ($_GET['a_formula'] == "ślubna") ? "czst" : "",
  "Przygotowanie darów" => $_GET["a_piesn_dary"],
  "Sanctus" => "czst",
  "Przemienienie" => "czst",
  "Ojcze nasz" => "czst",
  "Agnus Dei" => "czst",
  "Komunia 1" => $_GET["a_piesn_komunia1"],
  "Komunia 2" => $_GET["a_piesn_komunia2"],
  "Komunia 3" => $_GET["a_piesn_komunia3"],
  "Uwielbienie" => $_GET["a_piesn_uwielbienie"],
  "Błogosławieństwo" => "czst",
  "Zakończenie" => $_GET["a_piesn_zakonczenie"]
];

//czyszczenie i zakodowanie listy pieśni
$i = 0;
foreach($songlist as $name => $value){
  if($value == ""){
    unset($songlist[$name]);
    continue;
  }
  if(preg_match("/Komunia/", $name)) $name = "Komunia";
?>
songlist[<?php echo $i++; ?>] = ['<?php echo $name; ?>', `<?php echo $value; ?>`];
<?php }
unset($i);
?>

/* KOMPONENTY */

function Antyfona(props){
  return(
    <table className='antyfona'><tbody>
      <tr>
        <td>{props.ksiadz}</td>
        <td>→</td>
        <td dangerouslySetInnerHTML={{ __html : props.wierni}}></td>
      </tr>
    </tbody></table>
  )
}

function CzescStala({name}){
  const color = React.useContext(ColorContext);

  if(name == "post_aklamacja"){
    return <img className="czescstala" src={"../nuty/czescistale/"+name.toLowerCase().replace(/\s/g, "")+".png"} />;
  }else{
    return <img className="czescstala" src={"../nuty/czescistale/"+color+"_"+name.toLowerCase().replace(/\s/g, "")+".png"} />;
  }
}

function Everything(){
  const [color, setColor] = React.useState(window.czst_color);
  const [addmode, setAddmode] = React.useState(false);
  //gdybym coś usuwał, a potrzebuję innego state'a
  if(addmode < 0) setAddmode(false);
  
  return(
    <ColorContext.Provider value={color}>
      <div id="overlay">
        {addmode && <SongAdder setAddmode={setAddmode} wheretoadd={addmode} />}
        <RightSide setColor={setColor} />
      </div>
      <TitlePage color={color} />
      <Summary />
      {window.songlist.map((value, ind) =>{
        return( <SinglePage key={ind} page={ind+1} setAddmode={setAddmode} /> );
      })}
      <div className="page">
        <a href="#title" className="interactive">  
          <h2>Na początek</h2>
        </a>
      </div>
    </ColorContext.Provider>
  )
}

function Lyrics(props){
  let raw = props.raw;
  raw = raw.replace(/\*\*\n/g, '</span><br>');
  raw = raw.replace(/\*\n/g, '<span class="chorus">');
  raw = raw.replace(/_(.+)_/g, '<u>$1</u>');
  raw = raw.replace(/\d+\.\n/g, match => {return "<li start="+match.substring(0, match.length - 2)+">"});
  raw = raw.replace(/\n/g, "<br />");

  return(<ol className="lyrics" dangerouslySetInnerHTML={{ __html: raw}} />);
}

function RightSide(props) {
  /* settings panel */
  const color = React.useContext(ColorContext);
  const setColor = props.setColor;

  return (
    <div id="rightside">
      <div>
        <h4>Kolor cz. st.</h4>
        <select 
          value={color}
          onChange={val => {setColor(val.target.value);}}
        >
        {[
          ["zielony", "green"],
          ["biały", "white"],
          ["purpurowy", "purple"],
          ["czerwony (Piasecki)", "red"],
          ["niebieski (Pawlak)", "blue"],
          ["złoty (Machura)", "gold"]
        ].map(thing => {
          return <option key={thing[1]} value={thing[1]}>{thing[0]}</option>
        })}
        </select>
      </div>
    </div>
  );
}

function SinglePage({page, setAddmode}){
  return(
    <div className="page">
      <a id={"page" + page} />
      <Song page={page} setAddmode={setAddmode} />
    </div>
  )
}

function Song({page, setAddmode}){
  //const page = React.useContext(PageNumber);
  const [kiedy, title] = window.songlist[page - 1];
  
  switch(kiedy){
    case "Majowe/Czerwcowe":
      let flag = (title == "majowe");
      let subtitle = (flag) ? "Pod Twoją obronę" : "Do Serca Twojego";

      return(
        <>
          <h1>Nabożeństwo {title}</h1>
          <h2>Litania {flag ? "Loretańska" : "do Serca"}</h2>
          <img src={"../nuty/"+title+".png"} />
          <h2>Antyfona</h2>
          {
            flag ?
            <>
              <div className="alternative">
                <h4>Wybierz jedno:</h4>
                <div>
                  <Antyfona
                    ksiadz="Módl się za nami, święta Boża rodzicielko"
                    wierni="Abyśmy się stali godnymi obietnic chrystusowych"
                    />
                </div>
                <div>
                  <Antyfona
                    ksiadz="Raduj się i wesel, Panno Maryjo, Alleluja"
                    wierni="Bo zmartwychwstał prawdziwie, Alleluja"
                  />
                </div>
              </div>
              <p>Módlmy się: Panie nasz, Boże, dozwól nam, sługom swoim, cieszyć się trwałym zdrowiem duszy i ciała. I za wstawiennictwem Najświętszej Maryi zawsze dziewicy, uwolnij nas od doczesnych utrapień i obdarz wieczną radością, przez Chrystusa, Pana naszego...</p>
            </> :
            <>
              <Antyfona
                ksiadz="Jezu cichy i pokornego serca"
                wierni="Uczyń serca nasze według serca Twego"
              />
              <p>Módlmy się: wszechmogący, wieczny Boże, wejrzyj na Serce najmilszego Syna swego i na chwałę, i zadość uczynienie, jakie w imieniu grzeszników ci składa; daj się przebłagać tym, którzy żebrzą Twego miłosierdzia i racz udzielić przebaczenia w imię tegoż Syna swego, Jezusa Chrystusa, który z tobą żyje i króluje na wieki wieków...</p>
            </>
          }
          <h2>{subtitle}</h2>
          <Lyrics raw={window.piesni[subtitle]["tekst"]} />
        </>
      )
    case "Gorzkie żale":
      let gorzkie_tonacje = window.piesni["Gorzkie żale"]["tonacja"].split("/");
      return(
        <>
          <h1>Gorzkie żale</h1>
          <h2>Pobudka</h2>
          <h4>in {gorzkie_tonacje[0]}</h4>
          <img src="../nuty/gorzkieżale_pobudka.png" />
          <ol className="lyrics">
          {[
            "Gorzkie żale, przybywajcie * Serca nasze przenikajcie.",
            "Rozpłyńcie się, me źrenice * Toczcie smutnych łez krynice.",
            "Słońce, gwiazdy omdlewają * Żałobą się pokrywają.",
            "Płaczą rzewnie aniołowie * A któż żałość ich wypowie?",
            "Opoki się twarde krają * Z grobów umarli powstają",
            "Cóż jest, pytam, co się dzieje? * Wszystko stworzenie truchleje",
            "Na ból męki Chrystusowej * Żal przejmuje bez wymowy",
            "Uderz, Jezu, bez odwłoki * W twarde serc naszych opoki",
            "Jezu mój, we krwi ran Twoich * Obmyj duszę z grzechów moich",
            "Upał serca swego chłodzę * Gdy w przepaść męki Twej wchodzę."
          ].map((val, i) => <li key={i}>{val}</li>)}
          </ol>
          <h2>Intencja</h2>
          {[
            "Przy pomocy łaski Bożej przystępujemy do rozważania męki Pana naszego Jezusa Chrystusa. Ofiarować ją będziemy Ojcu niebieskiemu na cześć i chwałę Jego Boskiego Majestatu, pokornie Mu dziękując za wielką i niepojętą miłość ku rodzajowi ludzkiemu, iż raczył zesłać Syna swego, aby za nas wycierpiał okrutne męki i śmierć podjął krzyżową. To rozmyślanie ofiarujemy również ku czci Najświętszej Maryi Panny, Matki Bolesnej, oraz ku uczczeniu Świętych Pańskich, którzy wyróżniali się nabożeństwem ku Męce Chrystusowej.",
            "W pierwszej części będziemy rozważali, co Pan Jezus wycierpiał od modlitwy w Ogrójcu aż do niesłusznego przed sądem oskarżenia. Te zniewagi i zelżywości temuż Panu, za nas bolejącemu, ofiarujemy za Kościół święty katolicki, za najwyższego Pasterza z całym duchowieństwem, nadto za nieprzyjaciół krzyża Chrystusowego i wszystkich niewiernych, aby im Pan Bóg dał łaskę nawrócenia i opamiętania.",
            "W drugiej części rozmyślania męki Pańskiej będziemy rozważali, co Pan Jezus wycierpiał od niesłusznego przed sądem oskarżenia aż do okrutnego cierniem ukoronowania. Te zaś rany, zniewagi i zelżywości temuż Jezusowi cierpiącemu ofiarujemy, prosząc Go o pomyślność dla Ojczyzny naszej, o pokój i zgodę dla wszystkich narodów, a dla siebie o odpuszczenie grzechów, oddalenie klęsk i nieszczęść doczesnych, a szczególnie zarazy, głodu, ognia i wojny.",
            "W tej ostatniej części będziemy rozważali, co Pan Jezus wycierpiał od chwili ukoronowania aż do ciężkiego skonania na krzyżu. Te bluźnierstwa, zelżywości i zniewagi, jakie Mu wyrządzano, ofiarujemy za grzeszników zatwardziałych, aby Zbawiciel pobudził ich serca zbłąkane do pokuty i prawdziwej życia poprawy oraz za dusze w czyśćcu cierpiące, aby im litościwy Jezus krwią swoją świętą ogień zagasił; prosimy nadto, by i nam wyjednał na godzinę śmierci skruchę za grzechy i szczęśliwe w łasce Bożej wytrwanie."
          ].map((val, i) => <p key={i}>{val}</p>)}
          <h2>Hymn</h2>
          <h4>in {gorzkie_tonacje[1]}</h4>
          <img src="../nuty/gorzkieżale_hymn.png" />
          <Lyrics raw={
            `1.\nŻal duszę ściska, serce boleść czuje, * Gdy słodki Jezus na śmierć się gotuje;
            Klęczy w Ogrójcu, gdy krwawy pot leje, * Me serce mdleje.
            2.\nPana świętości uczeń zły całuje, *Żołnierz okrutny powrozmi krępuje!
            Jezus tym więzom dla nas się poddaje, * Na śmierć wydaje.
            3.\nBije, popycha tłum nieposkromiony, * Nielitościwie z tej i owej strony,
            Za włosy targa: znosi w cierpliwości * Król z wysokości.
            4.\nZsiniałe przedtem krwią zachodzą usta, * Gdy zbrojną żołnierz rękawicą chlusta;
            Wnet się zmieniło w płaczliwe wzdychanie * Serca kochanie.
            5.\nOby się serce we łzy rozpływało, * Że Cię, mój Jezu, sprośnie obrażało!
            Żal mi, ach, żal mi ciężkich moich złości * Dla Twej miłości!`
          } />
          <hr />
          <Lyrics raw={
            `1.\nPrzypatrz się, duszo, jak cię Bóg miłuje, * Jako dla ciebie sobie nie folguje.
            Przecież Go bardziej niż katowska, dręczy, * Złość twoja męczy.
            2.\nStoi przed sędzią Pan wszego stworzenia, * Cichy Baranek, z wielkiego wzgardzenia;
            Dla białej szaty, którą jest odziany, * Głupim nazwany.
            3.\nZa moje złości grzbiet srodze biczują; * Pójdźmyż, grzesznicy, oto nam gotują
            Ze Krwi Jezusa dla serca ochłody * Zdrój żywej wody.
            4.\nPycha światowa niechaj, co chce, wróży, * Co na swe skronie wije wieniec z róży,
            W szkarłat na pośmiech, cierniem Król zraniony, * Jest ozdobiony!
            5.\nOby się serce we łzy rozpływało, * Że Cię, mój Jezu, sprośnie obrażało!
            Żal mi, ach, żal mi ciężkich moich złości, * Dla Twej miłości!`
                      } />
                      <hr />
                      <Lyrics raw={
            `1.\nDuszo oziębła, czemu nie gorejesz? * Serce me, czemu całe nie truchlejesz?
            Toczy twój Jezus z ognistej miłości * Krew w obfitości.
            2.\nOgień miłości, gdy Go tak rozpala, * Sromotne drzewo na ramiona zwala;
            Zemdlony Jezus pod krzyżem uklęka, * Jęczy i stęka.
            3.\nOkrutnym katom posłuszny się staje, * Ręce i nogi przebić sobie daje,
            Wisi na krzyżu, ból ponosi srogi * Nasz Zbawca drogi!
            4.\nO słodkie drzewo, spuśćże nam już Ciało, * Aby na tobie dłużej nie wisiało!
            My je uczciwie w grobie położymy, * Płacz uczynimy.
            5.\nOby się serce we łzy rozpływało * Że Cię, mój Jezu, sprośnie obrażało!
            Żal mi, ach, żal mi ciężkich moich złości, * Dla Twej miłości!
            6.\nNiech Ci, mój Jezu, cześć będzie w wieczności * Za Twe obelgi, męki, zelżywości,
            Któreś ochotnie, Syn Boga jedyny, Cierpiał bez winy!`
          } />
          <h2>Lament duszy nad cierpiącym Jezusem</h2>
          <h4>in {gorzkie_tonacje[2]}</h4>
          <img src="../nuty/gorzkieżale_lament.png" />
          <Lyrics raw={
            `1.\nJezu, na zabicie okrutne, * Cichy Ba_ran_ku od wro_gów_ szukany, * Jezu mój kochany!
            2.\nJezu, za trzydzieści srebrników * Od niewdzięcz_ne_go ucznia _za_przedany, * Jezu mój kochany!
            3.\nJezu, w ciężkim smutku żałością, * Jakoś sam _wyz_nał, przed śmier_cią_ nękany, * Jezu mój kochany!
            4.\nJezu, na modlitwie w Ogrójcu * Strumieniem _po_tu krwawe_go_ zalany, * Jezu mój kochany!
            5.\nJezu, całowaniem zdradliwym * Od niegod_ne_go Juda_sza_ wydany, * Jezu mój kochany!
            6.\nJezu, powrozami grubymi * Od swawol_ne_go żołdact_wa_ związany, * Jezu mój kochany!
            7.\nJezu, od pospólstwa zelżywie * Przed Anna_szo_wym sądem _znie_ważany, * Jezu mój kochany!
            8.\nJezu, przez ulice sromotnie * Przed sąd Kaj_fa_sza za wło_sy_ targany, * Jezu mój kochany!
            9.\nJezu, od Malchusa srogiego * Ręką zbrod_ni_czą wypo_licz_kowany, * Jezu mój kochany!
            10.\nJezu, od fałszywych dwóch świadków * Za zwodzi_cie_la niesłusz_nie_ podany, * Jezu mój kochany!
            *\nBądź pozdrowiony, bądź pochwalony * Dla nas zelżony i pohańbiony
            Bądź uwielbiony! Bądź wysławiony! Boże nieskończony!`
          } />
          <hr />
          <Lyrics raw={
            `1.\nJezu, od pospólstwa niewinnie * Jako łotr _go_dzien śmierci _ob_wołany, * Jezu mój kochany!
            2.\nJezu, od złośliwych morderców * Po ślicznej _twa_rzy tak sproś_nie_ zeplwany, * Jezu mój kochany!
            3.\nJezu, pod przysięgą od Piotra * Po trzykroć z _wiel_kiej bojaź_ni_ zaprzany, * Jezu mój kochany!
            4.\nJezu, od okrutnych oprawców * Na sąd Pi_ła_ta, jak zbój_ca_ szarpany, * Jezu mój kochany!
            5.\nJezu, od Heroda i dworzan, * Królu nie_bies_ki, zelży_wie_ wyśmiany, * Jezu mój kochany!
            6.\nJezu, w białą szatę szydersko * Na większy _po_śmiech i hań_bę_ ubrany, * Jezu mój kochany!
            7.\nJezu, u kamiennego słupa * Niemiło_sier_nie biczmi _wy_smagany, * Jezu mój kochany!
            8.\nJezu, przez szyderstwo okrutne * Cierniowym _wień_cem uko_ro_nowany, * Jezu mój kochany!
            9.\nJezu, od żołnierzy niegodnie * Na pośmie_wis_ko purpu_rą_ odziany, * Jezu mój kochany!
            10.\nJezu, trzciną po głowie bity, * Królu bo_leś_ci, przez lud _wy_szydzany, * Jezu mój kochany!
            *\nBądź pozdrowiony, bądź pochwalony * Dla nas zelżony, wszystek skrwawiony
            Bądź uwielbiony! Bądź wysławiony! Boże nieskończony!`
          } />
          <hr />
          <Lyrics raw={
            `1.\nJezu, od pospólstwa niezbożnie * Jako zło_czyń_ca z łotry po_rów_nany, * Jezu mój kochany!
            2.\nJezu, przez Piłata niesłusznie * Na śmierć krzy_żo_wą za lu_dzi_ skazany, * Jezu mój kochany!
            3.\nJezu, srogim krzyża ciężarem * Na kalwa_ry_jskiej drodze _zmor_dowany, * Jezu mój kochany!
            4.\nJezu, do sromotnego drzewa * Przytępio_ny_mi gwoźdźmi _przy_kowany, * Jezu mój kochany!
            5.\nJezu, jawnie pośród dwu łotrów * Na drzewie _hań_by u_krzy_żowany, * Jezu mój kochany!
            6.\nJezu, od stojących wokoło * I przecho_dzą_cych szyder_czo_ wyśmiany, * Jezu mój kochany!
            7.\nJezu, bluźnierstwami od złego, * Współwiszą_ce_go łotra _wy_szydzany, * Jezu mój kochany!
            8.\nJezu, gorzką żółcią i octem * W wielkim pra_gnie_niu swoim _na_pawany, * Jezu mój kochany!
            9.\nJezu, w swej miłości niezmiernej * Jeszcze po _śmier_ci włócznią _prze_orany, * Jezu mój kochany!
            10.\nJezu, od Józefa uczciwie * I Niko_de_ma w grobie _po_chowany, * Jezu mój kochany!
            *\nBądź pozdrowiony, bądź pochwalony, * Dla nas zmęczony i krwią zbroczony.
            Bądź uwielbiony! Bądź wysławiony! Boże nieskończony!`
          } />
          <h2>Rozmowa duszy z Matką Bolesną</h2>
          <h4>in {gorzkie_tonacje[3]}</h4>
          <img src="../nuty/gorzkieżale_rozmowa.png" />
          <Lyrics raw={
            `1.\nAch! Ja Matka tak żałosna! * Boleść mnie ściska nieznośna. * Miecz me serce przenika.
            2.\nCzemuś, Matko ukochana, * Ciężko na sercu stroskana? * Czemu wszystka truchlejesz?
            3.\nCo mię pytasz? Wszystkam w mdłości, * Mówić nie mogę z żałości, * Krew mi serce zalewa.
            4.\nPowiedz mi, o Panno moja, * Czemu blednieje twarz Twoja? * Czemu gorzkie łzy lejesz?
            5.\nWidzę, że Syn ukochany * W Ogrójcu cały zalany * Potu krwawym potokiem.
            6.\nO Matko, źródło miłości, * Niech czuję gwałt Twej żałości! *Dozwól mi z sobą płakać.`
          } />
          <hr />
          <Lyrics raw={
            `1.\nAch, widzę Syna mojego * Przy słupie obnażonego, * Rózgami zsieczonego!
            2.\nŚwięta Panno, uproś dla mnie, * Bym ran Syna Twego znamię * Miał na sercu wyryte!
            3.\nAch, widzę jako niezmiernie * Ostre głowę ranią ciernie! * Dusza moja ustaje!
            4.\nO Maryjo, Syna swego, * Ostrym cierniem zranionego, * Podzielże ze mną mękę!
            5.\nObym ja, Matka strapiona, * Mogła na swoje ramiona * Złożyć krzyż Twój, Synu mój!
            6.\nProszę, o Panno jedyna, * Niechaj krzyż Twojego Syna * Zawsze w sercu swym noszę!`
          } />
          <hr />
          <Lyrics raw={
            `1.\nAch, Ja Matka boleściwa, * Pod krzyżem stoję smutliwa, * Serce żałość przejmuje.
            2.\nO Matko, niechaj prawdziwie, * Patrząc na krzyż żałośliwie, * Płaczę z Tobą rzewliwie.
            3.\nJużci, już moje Kochanie * Gotuje się na skonanie! * Toć i ja z Nim umieram!
            4.\nPragnę, Matko, zostać z Tobą, * Dzielić się Twoją żałobą * Śmierci Syna Twojego.
            5.\nZamknął słodką Jezus mowę, * Już ku ziemi skłania głowę, * Żegna już Matkę swoją!
            6.\nO Maryjo, Ciebie proszę, * Niech Jezusa rany noszę * I serdecznie rozważam.`
          } />
          <h2>Któryś za nas cierpiał rany</h2>
          <h4>
          {[
            window.piesni["Któryś za nas cierpiał rany"]["katsiedlecki"],
            window.piesni["Któryś za nas cierpiał rany"]["nr"],
            "in "+window.piesni["Któryś za nas cierpiał rany"]["tonacja"]
          ].join(" • ")}
          </h4>
          <Lyrics raw={window.piesni["Któryś za nas cierpiał rany"]["tekst"]} />
        </>
      )
    case "Kyrie":
      return(
        <>
          <Antyfona
            ksiadz="W imię Ojca i Syna i Ducha Świętego"
            wierni="Amen"
          />
          <Antyfona
            ksiadz="Pan z wami"
            wierni="I z duchem Twoim"
          />
          <h2>Akt pokutny</h2>
          <div className="alternative">
            <h4>Wybierz jedno:</h4>
            <div>
              <p className="ksiadz">Spowiadam się Bogu Wszechmogącemu...</p>
              <h1>Kyrie</h1>
              <CzescStala name={kiedy} />
              <Lyrics raw={
                `Panie, zmiłuj się nad nami
                Chryste, zmiłuj się nad nami
                Panie, zmiłuj się nad nami`
              } />
            </div>
            <div>
              <Antyfona
                ksiadz="...Zmiłuj się nad nami"
                wierni="Zmiłuj się nad nami"
              />
            </div>
          </div>
        </>
      )
    case "Gloria":
      return(
        <>
          <h1>Gloria</h1>
          <CzescStala name={kiedy} />
          <Lyrics raw={
            `Chwała na wysokości Bogu
            A na ziemi pokój ludziom dobrej woli
            Chwalimy Cię • Błogosławimy Cię
            Wielbimy Cię • Wysławiamy Cię
            Dzięki Ci składamy • Bo wielka jest chwała Twoja
            Panie Boże, królu nieba • Boże, Ojcze wszechmogący
            Panie, Synu jednorodzony • Jezu Chryste
            Panie Boże, Baranku Boży • Synu Ojca
            Który gładzisz grzechy świata • Zmiłuj się nad nami
            Który gładzisz grzechy świata • Przyjm błagania nasze
            Który siedzisz po prawicy Ojca • Zmiłuj się nad nami
            Albowiem tylko Tyś jest święty • Tylko Tyś jest Panem
            Tylko Tyś najwyższy • Jezu Chryste
            Z Duchem Świętym, w chwale Boga Ojca, amen`
          } />
          <p>
            
          </p>
          <Antyfona
            ksiadz="Módlmy się..."
            wierni="Amen"
          />
        </>
      )
    case "Credo":
      return(
        <>
          <h1>Credo</h1>
          <table className="credo"><tbody>
            <tr><td>Wierzę w jednego Boga, Ojca wszechmogącego, Stworzyciela nieba i ziemi</td></tr>
            <tr><td>Wszystkich rzeczy widzialnych i niewidzialnych</td></tr>
            <tr><td>I w jednego Pana Jezusa Chrystusa, Syna bożego Jednorodzonego</td></tr>
            <tr><td>Który z Ojca jest zrodzony przed wszystkimi wiekami</td></tr>
            <tr><td>Bóg z Boga, światłość ze światłości</td></tr>
            <tr><td>Bóg prawdziwy z Boga prawdziwego</td></tr>
            <tr><td>Zrodzony a nie stworzony, współistotny Ojcu</td></tr>
            <tr><td>A przez niego wszystko się stało</td></tr>
            <tr><td>On to dla nas ludzi i dla naszego zbawienia</td></tr>
            <tr><td>Zstąpił z nieba</td></tr>
            <tr><td>I za sprawą Ducha świętego</td></tr>
            <tr><td>Przyjął ciało z Maryi Dziewicy i stał się człowiekiem</td></tr>
            <tr><td>Ukrzyżowany również za nas</td></tr>
            <tr><td>Pod Poncjuszem Piłatem został umęczony i pogrzebany</td></tr>
            <tr><td>I zmartwychwstał dnia trzeciego, jak oznajmia pismo</td></tr>
            <tr><td>I wstąpił do nieba, siedzi po prawicy Ojca</td></tr>
            <tr><td>I powtórnie przyjdzie w chwale sądzić żywych i umarłych</td></tr>
            <tr><td>A królestwu jego nie będzie końca</td></tr>
            <tr><td>Wierzę w Ducha Świętego, Pana i Ożywiciela</td></tr>
            <tr><td>Który od ojca i syna pochodzi</td></tr>
            <tr><td>Który z ojcem i synem wspólnie odbiera uwielbienie i chwałę</td></tr>
            <tr><td>Który mówił przez proroków</td></tr>
            <tr><td>Wierzę w jeden, święty, powszechny i apostolski kościół</td></tr>
            <tr><td>Wyznaję jeden chrzest na odpuszczenie grzechów</td></tr>
            <tr><td>I oczekuję wskrzeszenia umarłych</td></tr>
            <tr><td>I życia wiecznego w przyszłym świecie, amen</td></tr>
          </tbody></table>
          <h2>Modlitwa powszechna</h2>
          <Antyfona 
            ksiadz="Ciebie prosimy"
            wierni="Wysłuchaj nas, Panie"
          />
        </>
      )
    case "Ślub":
      dane = [
        window.piesni["O Stworzycielu Duchu"]['katsiedlecki'],
        window.piesni["O Stworzycielu Duchu"]['nr'],
        "in " + window.piesni["O Stworzycielu Duchu"]['tonacja']
      ].join(" • ");

      return(
        <>
          <p className="ksiadz">Prośmy więc Ducha Świętego... ...Chrystusa i Kościoła</p>
          <h1>O Stworzycielu Duchu</h1>
          <h4>{dane}</h4>
          <Lyrics raw={window.piesni["O Stworzycielu Duchu"]["tekst"]} />
          <h2>Modlitwa powszechna</h2>
          <Antyfona 
            ksiadz="Ciebie prosimy"
            wierni="Wysłuchaj nas, Panie"
          />
        </>
      )
    case "Sanctus":
      return(
        <>
          <Antyfona 
            ksiadz="Módlmy się..."
            wierni="Amen"
          />
          <Antyfona 
            ksiadz="Pan z Wami"
            wierni="I z Duchem Twoim"
          />
          <Antyfona 
            ksiadz="W górę serca"
            wierni="Wznosimy je do Pana"
          />
          <Antyfona 
            ksiadz="Dzięki sładajmy Panu Bogu naszemu"
            wierni="Godne to i sprawiedliwe"
          />
          <h1>Sanctus</h1>
          <CzescStala name={kiedy} />
          <Lyrics raw={
            `Święty, Święty, Święty
            Pan Bóg zastępów
            Pełne są niebiosa
            I ziema chwały Twojej
            Hosanna na wysokości

            Błogosławiony
            Który idzie w imię Pańskie
            Hosanna na wysokości`
          } />
        </>
      )
    case "Przemienienie":
      return(
        <>
          <h1>Przemienienie</h1>
          <div className="alternative">
            <h4>Wybierz jedno:</h4>
            <div>
              <Antyfona 
              ksiadz="Oto wielka tajemnica wiary"
              wierni="Głosimy śmierć Twoją, Panie Jezu, <br />wyznajemy Twoje zmartwychwstanie <br />i oczekujemy Twego przyjścia w chwale"
              />
            </div>
            <div>
              <Antyfona 
              ksiadz="Tajemnica wiary"
              wierni="Chrystus umarł, <br />Chrystus zmartwychwstał, <br />Chrystus powróci"
              />
            </div>
            <div>
              <Antyfona 
              ksiadz="Wielka jest tajemnica naszej wiary"
              wierni="Ile razy ten chleb spożywamy <br />i pijemy z tego kielicha, <br />głosimy śmierć Twoją, Panie, <br />oczekując Twego przyjścia w chwale"
              />
            </div>
            <div>
              <Antyfona 
              ksiadz="Uwielbiajmy tajemnicę wiary"
              wierni="Panie, Ty nas wybawiłeś <br />przez krzyż i zmartwychwstanie swoje, <br />Ty jesteś zbawicielem świata"
              />
            </div>
          </div>
        </>
      )
    case "Ojcze nasz":
      return(
        <>
          <Antyfona 
            ksiadz="Przez Chrystusa, z Chrystusem i w Chrystusie..."
            wierni="Amen"
          />
          <p className="ksiadz">Nazywamy się dziećmi bożymi...</p>
          <h1>Ojcze nasz</h1>
          <Lyrics raw={
            `Ojcze nasz, któryś jest w niebie
            Święć się, imię Twoje
            Przyjdź Królestwo Twoje
            Bądź wola Twoja
            Jako w niebie, tak i na ziemi
            Chleba naszego powszedniego daj nam dzisiaj
            I odpuść nam nasze winy
            Jako i my odpuszczamy naszem winowajcom
            I nie wódź nas na pokuszenie
            Ale nas zbaw ode złego`
          } />
          <Antyfona 
            ksiadz="Wybaw nas, Panie, od zła wszelkiego..."
            wierni="Bo Twoje jest Królestwo, i potęga i chwała na wieki"
          />
        </>
      )
    case "Agnus Dei":
      return(
        <>
          <Antyfona
            ksiadz="Pokój Pański niech zawsze będzie z wami"
            wierni="I z duchem Twoim"
          />
          <p className="ksiadz">Przekażcie sobie znak pokoju</p>
          <h1>Agnus Dei</h1>
          <CzescStala name={kiedy} />
          <Lyrics raw={
            `Baranku Boży
            Który gładzisz grzechy świata
            Zmiłuj się nad nami
            
            Baranku Boży
            Który gładzisz grzechy świata
            Zmiłuj się nad nami
            
            Baranku Boży
            Który gładzisz grzechy świata
            Obdarz nas pokojem`
          } />
        </>
      )
    case "Błogosławieństwo":
      return(
        <>
          <Antyfona
            ksiadz="Módlmy się..."
            wierni="Amen"
          />
          <h1>Błogosławieństwo</h1>
          <Antyfona
            ksiadz="Pan z wami"
            wierni="I z duchem Twoim"
          />
          <Antyfona
            ksiadz="Niech was błogosławi Bóg Wszechmogący..."
            wierni="Amen"
          />
          <Antyfona
            ksiadz="Idźcie w pokoju Chrystusa"
            wierni="Bogu niech będą dzięki"
          />
        </>
      )
    case "Psalm":
    case "Aklamacja":
      return(
        <>
          <h1>{kiedy}</h1>
          {(kiedy == "Aklamacja" && window.a_formula == "Wielki Post") ?
            <CzescStala name="post_aklamacja" /> :
            <CzescStala name={kiedy} />
          }
          <div className="psalm">

          {title.split(/\n\n/).map(out => {
            return(
              <p>
                {out.split(/\n/).map(outt => {
                  return(
                    <>
                      {outt}<br />
                    </>
                  )
                })}
              </p>
            )
          })}
          </div>
        </>
      )
    default:
      let preferencje = window.piesni[title]['naco'].split("/");
      for(var i = 0; i < 5; i++){ if(preferencje[i] != 0) preferencje[i] = preferencje_names[i] }
      preferencje = preferencje.filter((val) => val != 0);
      if(preferencje.length == 0) preferencje = ["brak preferencji"];
      
      let dane = [
        window.piesni[title]['katsiedlecki'],
        window.piesni[title]['nr'],
        "in " + window.piesni[title]['tonacja'],
        preferencje.join(" • ")
      ].join(" • ");

      return(
        <>
          <div className="buttoncase abs_right">
            {kiedy == "Komunia" && <a className="button" href="#uwielbienie">U</a>}
            {kiedy == "Uwielbienie" && <a id="uwielbienie"></a>}
            <a className="button" onClick={() => setAddmode(page)}>+</a>
          </div>
          <div className="eraserButton buttoncase abs_left">
            <a className="button e_first">–</a>
            <a className="button e_second" onClick={() => {
              window.songlist.splice(page - 1, 1);
              setAddmode(-1);
            }}>&#x2713;</a>
          </div>
          <h2>{kiedy}</h2>
          <h1>{title.toUpperCase()}</h1>
          <h4>{dane}</h4>
          <img src={"../nuty/"+title+".png"} />
          <Lyrics raw={window.piesni[title]['tekst']} />
        </>
      )
  }
}

function SongAdder({setAddmode, wheretoadd}){
  const whereami = window.songlist[Math.max(wheretoadd - 1, 0)][0];
  const whereami_kody = {
    "Wejście" : /^1\/.\/.\/.\/./,
    "Przygotowanie darów" : /^.\/1\/.\/.\/./,
    "Komunia" : /^.\/.\/1\/.\/./,
    "Uwielbienie" : /^.\/.\/.\/1\/./,
    "Zakończenie" : /^.\/.\/.\/.\/1/
  };

  // sugestie pieśni
  var piesni_sugg = {
    "Fitting" : [],
    "Okresowe" : [],
    "Maryjne" : [],
    "Do Serca" : [],
    "Nietypowe" : []
  };

  console.log(whereami_kody[whereami]);
  for(const song of Object.keys(window.piesni)){
    // sugestia na podstawie tego, gdzie jestem
    if(window.piesni[song]["naco"].match(new RegExp(whereami_kody[whereami]))){
      piesni_sugg["Fitting"][song] = window.piesni[song];
    }
    if(window.piesni[song]["klasa"] == window.okazja && window.okazja != 0){
      piesni_sugg["Okresowe"][song] = window.piesni[song];
    }
    if(window.piesni[song]["klasa"] == 3){
      piesni_sugg["Maryjne"][song] = window.piesni[song];
    }
    if(window.piesni[song]["klasa"] == 4){
      piesni_sugg["Do Serca"][song] = window.piesni[song];
    }
    if(window.piesni[song]["klasa"] == 2){
      piesni_sugg["Nietypowe"][song] = window.piesni[song];
    }
  }

  function songAdd(addwhat){
    window.songlist.splice(wheretoadd, 0, [whereami, addwhat.target.value]);
    setAddmode(false);
  }

  function SongSuggestions({header, list}){
    return(
      <div>
        <h4>{header}</h4>
        <select onChange={songAdd}>
          <option value=""></option>
        {Object.keys(list).map((value, index) => {
          return <option key={index} value={value}>{value}</option>;
        })}
        </select>
      </div>
    )
  }

  return(
    <div id="songadder">
      <h1>Dodaj pieśń</h1>
      <div className="a_container">
        <SongSuggestions header={"Pasujące na "+whereami} list={piesni_sugg["Fitting"]} />
        {window.okazja != 0 && <SongSuggestions header={"Pasujące na "+window.a_formula} list={piesni_sugg["Okresowe"]} />}
      </div>
      <div className="a_container">
        <SongSuggestions header={"Maryjne"} list={piesni_sugg["Maryjne"]} />
        <SongSuggestions header={"Do Serca"} list={piesni_sugg["Do Serca"]} />
      </div>
      <div className="a_container">
        <SongSuggestions header={"Z reguły na mszy nie grane"} list={piesni_sugg["Nietypowe"]} />
        <SongSuggestions header={"Dowolne"} list={window.piesni} />
      </div>
      <a className="button" onClick={() => setAddmode(false)}>×</a>
    </div>
  );
}

function Summary(){
  return(
    <div className="page">
      <h2>W skrócie</h2>
      <div className="summary">
      {window.songlist.map((value, ind) => {
        let [kiedy, co] = value;
        if(co != "czst" && kiedy != "Aklamacja"){
          if(kiedy == "Psalm") co = co.substring(0, co.indexOf("\n")); //pierwsza linijka
          return(
            <a key={ind} href={"#page" + (ind+1)} className="interactive">
              <h4>{kiedy}</h4>
              <h3>{co}</h3>
            </a>
          );
        }
      })}
      </div>
    </div>
  )
}

function TitlePage({color}){
  return(
    <div style={{background: `linear-gradient(${color}, white 50%)`}}>
      <h1 id="title">Szpiewnik Szybkiego Szukania 2</h1>
      <h4>
      Przygotowany na: {window.a_identyfikator}, formuła: {window.a_formula}
      </h4>
    </div>
  );
}
    
const root = ReactDOM.createRoot(document.getElementById("main"));
root.render(<Everything />);
</script>
  