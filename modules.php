<?php
$conn = new mysqli("localhost", "root", "", "szszsz");
if($conn->connect_error) echo "Nie można połączyć się z bazą: ".$conn->connect_error;
$conn->set_charset("utf8");
?>
<script type="text/babel">
/*
 * TODO
 * - dodawanie pieśni w locie
 * - formuły: ślubna, majowa, czerwcowa, pogrzebowa, adwentowa itd...
 * - 
 */
/* BETONIARKA DANYCH */

var piesni = [];

<?php
switch($_GET['a_formula']){
  case "Adwent":
    $inneokazje .= ", 5"; break;
  case "Boże Narodzenie":
    $inneokazje .= ", 6"; break;
  case "Wielki Post":
    $inneokazje .= ", 7"; break;
  case "zwykła wielkanocna":
  case "Wielkanoc":
    $inneokazje .= ", 8"; break;
  default:
    $inneokazje = "";
}

$q = "SELECT * 
      FROM pieśni p 
      LEFT JOIN kategorie k ON p.klasa = k.kategoria 
      WHERE p.klasa IN (1, 2, 3, 4$inneokazje)
      ORDER BY p.klasa, p.tytuł";
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

var a_formula = "<?php echo $_GET['a_formula']; ?>";

/* KOMPONENTY */

const PageNumber = React.createContext();
const ColorContext = React.createContext();

function TitlePage(){
  const date = new Date();
  let today = date.getDate() + "-" + ("0"+(date.getMonth()+1)).slice(-2) + "-" + date.getFullYear();

  let a_identyfikator = "<?php echo $_GET['a_identyfikator']; ?>";
  return(
    <>
      <h1 id="title">Szpiewnik Szybkiego Szukania 2</h1>
      <h4>
      Przygotowany na dzień {today},<br />
      tj. {a_identyfikator}, formuła {window.a_formula}
      </h4>
    </>
  );
}

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

function Albo(){
  return(<p className='albo'>albo</p>)
}

function CzescStala({name}){
  const color = React.useContext(ColorContext);

  if(name == "post_aklamacja"){
    return <img className="czescstala" src={"czescistale/"+name.toLowerCase().replace(/\s/g, "")+".png"} />;
  }else{
    return <img className="czescstala" src={"czescistale/"+color+"_"+name.toLowerCase().replace(/\s/g, "")+".png"} />;
  }
}

var songlist = new Array();
<?php
/* lista pieśni */
$songlist = [
  "Przed mszą" => $_GET["a_pre"],
  "Wejście" => $_GET["a_piesn_wejscie"],
  "Kyrie" => "czst",
  "Przed Gloria" => $_GET["a_piesn_przedgloria"],
  "Gloria" => (in_array($_GET["a_formula"], ["Adwent", "Wielki Post"])) ? "" : "czst",
  "Psalm" => $_GET["a_psalm"],
  "Sekwencja wielkanocna" => ($_GET["a_formula"] == "zwykła wielkanocna") ? "Niech w święto radosne" : "",
  "Aklamacja" => $_GET["a_aklamacja"],
  "Credo" => "czst",
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
?>
songlist[<?php echo $i++; ?>] = ['<?php echo $name; ?>', `<?php echo $value; ?>`];
<?php }
unset($i);
?>

var preferencje_names = ["Wejście", "Przygotowanie darów", "Komunia", "Uwielbienie", "Zakończenie"];
const czst_color = "<?php echo $_GET['a_czescistale']; ?>";

function Lyrics(props){
  let raw = props.raw.replace(/Ref\.\n/g, '<span class="chorus">');
  raw = raw.replace(/\d\.\n/g, match => {return "<li start="+match.substring(0, match.length - 2)+">"});
  raw = raw.replace(/\n/g, "<br />");

  return(<ol className="lyrics" dangerouslySetInnerHTML={{ __html: raw}} />);
}

function Song(){
  const page = React.useContext(PageNumber);
  const [kiedy, title] = window.songlist[page - 1];
  
  switch(kiedy){
    case "Kyrie":
      return(
        <>
          <h1>Kyrie</h1>
          <Antyfona
            ksiadz="W imię Ojca i Syna i Ducha Świętego"
            wierni="Amen"
          />
          <Antyfona
            ksiadz="Pan z wami"
            wierni="I z duchem Twoim"
          />
          <h2>Akt pokutny</h2>
          <p className="ksiadz">Spowiadam się Bogu Wszechmogącemu...</p>
          <Albo />
          <Antyfona
            ksiadz="...Zmiłuj się nad nami"
            wierni="Zmiłuj się nad nami"
          />
          <Albo />
          <CzescStala name={kiedy} />
          <p>
            Panie, zmiłuj się nad nami<br />
            Chryste, zmiłuj się nad nami<br />
            Panie, zmiłuj się nad nami
          </p>
        </>
      )
    case "Gloria":
      return(
        <>
          <h1>Gloria</h1>
          <CzescStala name={kiedy} />
          <p>
            Chwała na wysokości Bogu<br />
            A na ziemi pokój ludziom dobrej woli<br />
            Chwalimy Cię • Błogosławimy Cię<br />
            Wielbimy Cię • Wysławiamy Cię<br />
            Dzięki Ci składamy • Bo wielka jest chwała Twoja<br />
            Panie Boże, królu nieba • Boże, Ojcze wszechmogący<br />
            Panie, Synu jednorodzony • Jezu Chryste<br />
            Panie Boże, Baranku Boży • Synu Ojca<br />
            Który gładzisz grzechy świata • Zmiłuj się nad nami<br />
            Który gładzisz grzechy świata • Przyjm błagania nasze<br />
            Który siedzisz po prawicy Ojca • Zmiłuj się nad nami<br />
            Albowiem tylko Tyś jest święty • Tylko Tyś jest Panem<br />
            Tylko Tyś najwyższy • Jezu Chryste<br />
            Z Duchem Świętym, w chwale Boga Ojca, amen
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
          <p>
            Święty, Święty, Święty<br />
            Pan Bóg zastępów<br />
            Pełne są niebiosa<br />
            I ziema chwały Twojej<br />
            Hosanna na wysokości
          </p>
          <p>
            Błogosławiony<br />
            Który idzie w imię Pańskie<br />
            Hosanna na wysokości
          </p>
        </>
      )
    case "Przemienienie":
      return(
        <>
          <h1>Przemienienie</h1>
          <Antyfona 
            ksiadz="Oto wielka tajemnica wiary"
            wierni="Głosimy śmierć Twoją, Panie Jezu, <br />wyznajemy Twoje zmartwychwstanie <br />i oczekujemy Twego przyjścia w chwale"
          />
          <Albo />
          <Antyfona 
            ksiadz="Tajemnica wiary"
            wierni="Chrystus umarł, <br />Chrystus zmartwychwstał, <br />Chrystus powróci"
          />
          <Albo />
          <Antyfona 
            ksiadz="Wielka jest tajemnica naszej wiary"
            wierni="Ile razy ten chleb spożywamy <br />i pijemy z tego kielicha, <br />głosimy śmierć Twoją, Panie, <br />oczekując Twego przyjścia w chwale"
          />
          <Albo />
          <Antyfona 
            ksiadz="Uwielbiajmy tajemnicę wiary"
            wierni="Panie, Ty nas wybawiłeś <br />przez krzyż i zmartwychwstanie swoje, <br />Ty jesteś zbawicielem świata"
          />

          <hr />
          <Antyfona 
            ksiadz="Przez Chrystusa, z Chrystusem i w Chrystusie..."
            wierni="Amen"
          />
        </>
      )
    case "Ojcze nasz":
      return(
        <>
          <p className="ksiadz">Nazywamy się dziećmi bożymi...</p>
          <h1>Ojcze nasz</h1>
          <p>
            Ojcze nasz, któryś jest w niebie<br />
            Święć się, imię Twoje<br />
            Przyjdź Królestwo Twoje<br />
            Bądź wola Twoja<br />
            Jako w niebie, tak i na ziemi<br />
            Chleba naszego powszedniego daj nam dzisiaj<br />
            I odpuść nam nasze winy<br />
            Jako i my odpuszczamy naszem winowajcom<br />
            I nie wódź nas na pokuszenie<br />
            Ale nas zbaw ode złego
          </p>
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
          <p>
            Baranku Boży<br />
            Który gładzisz grzechy świata<br />
            Zmiłuj się nad nami
          </p>
          <p>
            Baranku Boży<br />
            Który gładzisz grzechy świata<br />
            Zmiłuj się nad nami
          </p>
          <p>
            Baranku Boży<br />
            Który gładzisz grzechy świata<br />
            Obdarz nas pokojem
          </p>
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
      
      const dane = [
        window.piesni[title]['katsiedlecki'],
        window.piesni[title]['nr'],
        "in " + window.piesni[title]['tonacja'],
        preferencje.join(" • ")
      ].join(" • ");

      return(
        <>
          <h2>{kiedy}</h2>
          <h1>{title}</h1>
          <h4>{dane}</h4>
          <img src={"nuty/"+title+".png"} />
          <Lyrics raw={window.piesni[title]['tekst']} />
        </>
      )
  }
}

function RightSide(props) {
  const page = React.useContext(PageNumber);
  const color = React.useContext(ColorContext);

  return (
    <div id="rightside">
      <div>
        <h4>Kolor</h4>
        <select 
          value={color}
          onChange={val => {props.setColor(val.target.value);}}
        >
        {[
          ["zielony", "green"],
          ["biały", "white"],
          ["purpurowy", "purple"],
          ["czerwony (Piasecki)", "red"],
          ["niebieski (Pawlak)", "blue"]
        ].map(thing => {
          return <option key={thing[1]} value={thing[1]}>{thing[0]}</option>
        })}
        </select>
      </div>
      <ol id="list">
      {songlist.map((val, i) => {
        if(["czst"].includes(val[1]) || ["Psalm", "Aklamacja"].includes(val[0])){
          return (i+1 == page) ? <li className="currentpage" key={i}>{val[0]}</li> : <li key={i}>{val[0]}</li>;
        }else{
          return (i+1 == page) ? <li className="currentpage" key={i}>{val[1]}</li> : <li key={i}>{val[1]}</li>;
        }
      })}
      </ol>
    </div>
  );
}

function CurrentPage(){
  const [color, setColor] = React.useState(window.czst_color);

  return(
    <ColorContext.Provider value={color}>
      <RightSide setColor={setColor} />
      <Song />
    </ColorContext.Provider>
  )
}

function SongAdder({setAddmode}){
  const page = React.useContext(PageNumber);
  const whereami = window.songlist[Math.max(page - 1, 0)][0];
  const whereami_kody = {
    "Wejście" : "1/./././.",
    "Przygotowanie darów" : "./1/././.",
    "Komunia 1" : "././1/./.",
    "Komunia 2" : "././1/./.",
    "Komunia 3" : "././1/./.",
    "Uwielbienie" : "./././1/.",
    "Zakończenie" : "././././1"
  };

  // sugestie pieśni
  var piesni_sugg = {"Fitting" : [], "Maryjne" : [], "Do Serca" : []};
  for(const song of Object.keys(window.piesni)){
    // sugestia na podstawie tego, gdzie jestem
    if(window.piesni[song]["naco"].match(whereami_kody[whereami])){
      piesni_sugg["Fitting"][song] = window.piesni[song];
    }
    if(window.piesni[song]["klasa"] == 3){
      piesni_sugg["Maryjne"][song] = window.piesni[song];
    }
    if(window.piesni[song]["klasa"] == 4){
      piesni_sugg["Do Serca"][song] = window.piesni[song];
    }
  }

  function songAdd(addwhat){
    window.songlist.splice(page, 0, ["Dodana", addwhat.target.value]);
    setAddmode(false);
  }

  function SongSuggestions({header, list}){
    return(
      <>
        <h2>{header}</h2>
        <select onChange={songAdd}>
          <option value=""></option>
        {Object.keys(list).map((value, index) => {
          return <option key={index} value={value}>{value}</option>;
        })}
        </select>
      </>
    )
  }

  return(
    <div id="songadder">
      <h1>Dodaj pieśń</h1>
      <SongSuggestions header={"Pasujące na "+whereami} list={piesni_sugg["Fitting"]} />
      <SongSuggestions header={"Maryjne"} list={piesni_sugg["Maryjne"]} />
      <SongSuggestions header={"Do Serca"} list={piesni_sugg["Do Serca"]} />
      <SongSuggestions header={"Dowolne"} list={window.piesni} />
      <a className="button" onClick={() => setAddmode(false)}>×</a>
    </div>
  );
}

function Everything(){
  const [pageno, setPageno] = React.useState(0);
  const [addmode, setAddmode] = React.useState(false);
  
  return(
    <PageNumber.Provider value={pageno}>
      <div id="overlay">
        {addmode && <SongAdder setAddmode={setAddmode} />}
        <a className="button" onClick={() => {if(pageno > 0) setPageno(pageno - 1);}}>«</a>
        <a className="button" onClick={() => setAddmode(true)}>+</a>
        <a className="button" onClick={() => {if(pageno < window.songlist.length) setPageno(pageno + 1);}}>»</a>
      </div>
      <div className="page currentpage">
      {(pageno == 0) ? <TitlePage /> : <CurrentPage /> }
      </div>
    </PageNumber.Provider>
  )
}

const root = ReactDOM.createRoot(document.getElementById("main"));
root.render(<Everything />);
</script>
  