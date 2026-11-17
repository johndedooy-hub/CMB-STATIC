/* CMB STATIC – luxe maar licht gehouden client. */
(function(){'use strict';
const $=id=>document.getElementById(id); const opt=window.CMB_STATIC||{};
const audio=$('radioStream'),play=$('playPauseBtn'),vol=$('volumeSlider'),volBtn=$('volumeBtn'),panel=$('volumePanel');
const profiles=Array.isArray(opt.streams)?opt.streams:[{streamUrl:opt.stream},{streamUrl:opt.backup}];
let started=false,activeIndex=0,altIndex=0,loadingStream=false,stallTimer=null,lastVolume=Number(opt.volume??75),trafficLoaded=false,ovLoaded=false;
const currentProfile=()=>profiles[activeIndex]||{};
const currentSources=()=>[currentProfile().streamUrl||'',currentProfile().streamUrlAlt||''].filter(Boolean);
const setPlaying=()=>{if(!play||!audio)return;const playing=!audio.paused;play.textContent=playing?'Ⅱ':'▶';play.classList.toggle('is-playing',playing);play.setAttribute('aria-label',playing?'Radio pauzeren':'Radio starten');};
const clearStall=()=>{if(stallTimer){clearTimeout(stallTimer);stallTimer=null;}};
async function openStream(which,alt=0){
 if(!audio||loadingStream||!started)return false;
 const p=profiles[which]||{},urls=[p.streamUrl||'',p.streamUrlAlt||''].filter(Boolean),url=urls[alt];
 if(!url)return false;
 loadingStream=true;clearStall();activeIndex=which;altIndex=alt;
 audio.pause();audio.src=url;audio.load();
 try{await audio.play();setPlaying();loadingStream=false;getMetadata();return true;}catch(e){loadingStream=false;setPlaying();return false;}
}
async function tryNext(){
 if(!started||loadingStream)return;
 let which=activeIndex,alt=altIndex+1;
 if(alt>=currentSources().length){which=1;alt=0;}
 if(which===1&&(!opt.failover||!profiles[1]||!(profiles[1].streamUrl||profiles[1].streamUrlAlt))){started=false;setPlaying();return;}
 if(!(await openStream(which,alt))){if(which===0)await tryNext();else if(alt===0&&(profiles[1].streamUrlAlt||''))await openStream(1,1);}
}
async function beginStream(){if(!audio)return;started=true;activeIndex=0;altIndex=0;if(!(await openStream(0,0)))await tryNext();}
if(audio&&play){
 audio.volume=Math.max(0,Math.min(1,lastVolume/100));
 play.addEventListener('click',async()=>{if(!audio.paused){started=false;clearStall();audio.pause();setPlaying();return;}if(!started||!audio.src){await beginStream();return;}started=true;try{await audio.play();}catch(e){await tryNext();}setPlaying();});
 audio.addEventListener('playing',()=>{clearStall();setPlaying();}); audio.addEventListener('pause',setPlaying); audio.addEventListener('error',()=>{if(started&&!loadingStream)tryNext();});
 const stall=()=>{if(!started||loadingStream||stallTimer)return;stallTimer=setTimeout(()=>{stallTimer=null;if(started&&!loadingStream&&audio.readyState<3)tryNext();},14000);};
 audio.addEventListener('waiting',stall); audio.addEventListener('stalled',stall);
}
async function recheckPrimary(){if(!started||activeIndex!==1||loadingStream||document.hidden)return;try{const res=await fetch('stream-check.php',{cache:'no-store'});if(!res.ok)return;const data=await res.json();if(data.ok){if(!(await openStream(0,0))&&profiles[1]?.streamUrl)await openStream(1,0);}}catch(e){}}
setInterval(recheckPrimary,Math.max(20000,Math.min(600000,Number(opt.recheck||60)*1000)));
if(vol&&audio){vol.value=String(lastVolume);if($('volumeValue'))$('volumeValue').textContent=lastVolume+'%';vol.addEventListener('input',()=>{lastVolume=Number(vol.value);audio.volume=lastVolume/100;if($('volumeValue'))$('volumeValue').textContent=lastVolume+'%';});}
if(volBtn&&panel){volBtn.addEventListener('click',()=>{const open=panel.hidden;panel.hidden=!open;volBtn.setAttribute('aria-expanded',String(open));});document.addEventListener('click',e=>{if(!panel.hidden&&!e.target.closest('.player-controls')){panel.hidden=true;volBtn.setAttribute('aria-expanded','false');}});}

const clock=$('liveClock'),date=$('liveDate');
function drawClock(){const now=new Date(),base={timeZone:'Europe/Amsterdam'};if(clock)clock.textContent=new Intl.DateTimeFormat('nl-NL',{...base,hour:'2-digit',minute:'2-digit',...(opt.seconds?{second:'2-digit'}:{})}).format(now);if(date){const compact=window.matchMedia('(max-width: 760px)').matches;date.textContent=new Intl.DateTimeFormat('nl-NL',{...base,weekday:compact?'short':'long',day:'numeric',month:compact?'short':'long',year:'numeric'}).format(now);}}drawClock();setInterval(drawClock,1000);

let weatherWorking=false,metaWorking=false; const weatherUI=$('weatherVisual');
function weatherClass(code){if([95,96,99].includes(code))return 'weather-stormy';if([51,53,55,56,57,61,63,65,66,67,80,81,82].includes(code))return 'weather-rainy';if([71,73,75,77,85,86].includes(code))return 'weather-snowy';if([45,48].includes(code))return 'weather-foggy';if(code===0)return 'weather-sunny';if([1,2].includes(code))return 'weather-partly';return 'weather-cloudy';}
async function getWeather(){if(weatherWorking||document.hidden)return;weatherWorking=true;try{const response=await fetch('weather.php',{cache:'default'});if(!response.ok)throw Error('weer');const data=await response.json();$('weatherCity').textContent=data.city||'';$('weatherDescription').textContent=data.ok?'· '+(data.description||''):'';$('weatherTemp').textContent=data.ok&&typeof data.temperature==='number'?data.temperature+'°':'--°';$('weatherStatus').textContent=data.ok?(data.description||'WEER').toUpperCase():'WEER';if($('panelWeatherTemp'))$('panelWeatherTemp').textContent=data.ok&&typeof data.temperature==='number'?data.temperature+'°':'--°';if($('panelWeatherTitle'))$('panelWeatherTitle').textContent=data.city||'';if($('panelWeatherText'))$('panelWeatherText').textContent=data.ok?(data.description||'Actueel'):'Geen actuele gegevens';if(weatherUI)weatherUI.className='weather-visual '+(data.ok?weatherClass(Number(data.code)):'weather-cloudy')+(opt.weatherAnimation?'':' no-weather-motion');}catch(e){if($('weatherTemp'))$('weatherTemp').textContent='--°';if($('panelWeatherText'))$('panelWeatherText').textContent='Geen actuele gegevens';}finally{weatherWorking=false;}}
getWeather();setInterval(getWeather,12*60*1000);

async function getMetadata(){if(metaWorking||document.hidden)return;metaWorking=true;try{const res=await fetch('track.php?stream='+(activeIndex+1),{cache:'default'});if(!res.ok)throw Error('track');const data=await res.json();if(data.title&&$('trackTitle'))$('trackTitle').textContent=data.title;if(data.artist&&$('trackArtist'))$('trackArtist').textContent=data.artist;const cover=$('trackCover');const fallback=$('coverFallback');if(cover&&data.cover&&data.cover!==cover.getAttribute('src')){cover.src=data.cover;cover.hidden=false;if(fallback)fallback.hidden=true;}}catch(e){}finally{metaWorking=false;}}
getMetadata();setInterval(getMetadata,20000);

const slides=Array.from(document.querySelectorAll('.slideshow-shell .slide')),dots=$('slideDots'),counter=$('slideCounter'),out=$('slideOutside');let index=0;
function showSlide(i){if(!slides.length)return;index=(i+slides.length)%slides.length;slides.forEach((slide,n)=>{slide.classList.toggle('active',n===index);slide.setAttribute('aria-hidden',String(n!==index));});if(counter)counter.textContent=String(index+1).padStart(2,'0')+' / '+String(slides.length).padStart(2,'0');if(dots)Array.from(dots.children).forEach((d,n)=>{d.classList.toggle('active',n===index);d.setAttribute('aria-current',String(n===index));});const s=slides[index],mode=s.dataset.caption||'overlay';const external=mode==='outside'||mode==='both';if(out){out.hidden=!external;if(external){$('slideOutsideTitle').textContent=s.dataset.title||'';$('slideOutsideSubtitle').textContent=s.dataset.subtitle||'';out.style.setProperty('--out-color',s.dataset.color||'#ffffff');out.style.setProperty('--out-subcolor',s.dataset.subcolor||'#ecf3f5');out.style.setProperty('--out-size',Math.min(24,Math.max(12,Number(s.dataset.fontsize||33)*0.53))+'px');}}}
if(dots&&slides.length>1){slides.forEach((s,n)=>{const b=document.createElement('button');b.type='button';b.setAttribute('aria-label','Slide '+(n+1));b.addEventListener('click',()=>showSlide(n));dots.appendChild(b);});}
showSlide(0); $('slidePrev')?.addEventListener('click',()=>showSlide(index-1)); $('slideNext')?.addEventListener('click',()=>showSlide(index+1));
setInterval(()=>{if(!document.hidden&&slides.length>1&&!overlayIsOpen())showSlide(index+1);},Math.max(3000,Math.min(60000,Number(opt.interval||8)*1000)));

$('menuToggle')?.addEventListener('click',()=>{const menu=$('navList');const open=menu.classList.toggle('open');$('menuToggle').setAttribute('aria-expanded',String(open));});

const overlayShell=$('overlayShell');
function overlayIsOpen(){return !!overlayShell && !overlayShell.hidden;}
function openOverlay(panelName,title){if(!overlayShell)return;overlayShell.hidden=false;$('slideshowShell')?.classList.add('overlay-active');const visiblePanel=Array.from(document.querySelectorAll('.overlay-panel')).find(p=>p.dataset.panel===panelName);Array.from(document.querySelectorAll('.overlay-panel')).forEach(p=>p.classList.toggle('active',p===visiblePanel));if(visiblePanel){visiblePanel.scrollTop=0;try{visiblePanel.focus({preventScroll:true});}catch(e){visiblePanel.focus();}}if($('overlayTitle'))$('overlayTitle').textContent=title||'Studio overzicht';if(panelName==='traffic'){loadTraffic();loadOv();}}
function closeOverlay(){if(overlayShell)overlayShell.hidden=true;$('slideshowShell')?.classList.remove('overlay-active');}
$('overlayClose')?.addEventListener('click',closeOverlay);
document.querySelectorAll('.nav-list a').forEach(a=>a.addEventListener('click',e=>{const panelName=a.dataset.panel||'home';if(panelName&&panelName!=='external'){e.preventDefault();openOverlay(panelName,(a.querySelector('.nav-num')?.nextElementSibling?.textContent||a.textContent||'').trim());if(innerWidth<=760){$('navList')?.classList.remove('open');$('menuToggle')?.setAttribute('aria-expanded','false');}}}));
document.querySelectorAll('[data-lightbox-link]').forEach(a=>a.addEventListener('click',e=>{const href=a.getAttribute('href')||'';if(!href.startsWith('#'))return;e.preventDefault();const map={home:'home',nieuws:'news',weer:'weather',regio:'region',verkeer:'traffic',contact:'extra',over:'extra'};openOverlay(map[href.slice(1)]||'home',a.textContent.trim());}));
document.addEventListener('keydown',e=>{if(e.key==='Escape'&&overlayIsOpen())closeOverlay();});

const plBtn=$('plToggle'),pl=$('plPanel');if(plBtn&&pl){plBtn.addEventListener('click',()=>{const open=pl.hidden;pl.hidden=!open;plBtn.setAttribute('aria-expanded',String(open));});}
const dialog=$('privacyDialog');$('privacyBtn')?.addEventListener('click',()=>{if(typeof dialog.showModal==='function')dialog.showModal();else dialog.setAttribute('open','');});$('closePrivacy')?.addEventListener('click',()=>dialog.close());
/* Reserve space equal to the rendered fixed dock, even with a custom ticker font. */
const dock=$('bottomDock');
if(dock){
  let reserved=-1;
  const reserveDock=()=>{const height=Math.ceil(dock.getBoundingClientRect().height)+14;
    if(height!==reserved){reserved=height;document.documentElement.style.setProperty('--dock-clearance',height+'px');}};
  reserveDock();
  if('ResizeObserver' in window)new ResizeObserver(reserveDock).observe(dock);
  else window.addEventListener('resize',reserveDock,{passive:true});
}
const ticker=$('tickerItems');if(ticker){const original=ticker.innerHTML;const children=ticker.querySelectorAll('.ticker-entry');if(children.length){ticker.innerHTML+=original;const len=Math.max(30,ticker.scrollWidth/2);const speed=Number(opt.tickerSpeed||60);ticker.style.setProperty('--ticker-duration',Math.max(15,Math.round(len/speed))+'s');ticker.classList.add('is-moving');}}

document.addEventListener('visibilitychange',()=>{const v=$('studio-video');if(v){if(document.hidden)v.pause();else v.play().catch(()=>{});}if(!document.hidden){getWeather();getMetadata();}});
const video=$('studio-video');if(video)video.addEventListener('error',()=>{video.pause();video.removeAttribute('src');video.querySelectorAll('source').forEach(s=>s.removeAttribute('src'));video.load();video.hidden=true;});

function renderTrafficItems(el,items,emptyText){if(!el)return;if(!items||!items.length){el.innerHTML='<p class="traffic-empty">'+emptyText+'</p>';return;}el.innerHTML=items.slice(0,8).map(item=>'<article class="traffic-item"><strong>'+escapeHtml(item.title||item.road||'Melding')+'</strong><div>'+escapeHtml(item.text||item.description||'')+'</div>'+(item.meta?'<small>'+escapeHtml(item.meta)+'</small>':'')+'</article>').join('');}
function escapeHtml(str){const div=document.createElement('div');div.textContent=String(str||'');return div.innerHTML;}
async function loadTraffic(){if(trafficLoaded)return;trafficLoaded=true;try{const res=await fetch(opt.trafficUrl||'traffic.php',{cache:'default'});const data=await res.json();renderTrafficItems($('trafficRoad'),data.items||[],data.message||'Geen verkeersmeldingen beschikbaar.');}catch(e){renderTrafficItems($('trafficRoad'),[], 'Geen verkeersmeldingen beschikbaar.');}}
async function loadOv(){if(ovLoaded)return;ovLoaded=true;try{const res=await fetch(opt.ovUrl||'ov.php',{cache:'default'});const data=await res.json();renderTrafficItems($('trafficTrain'),data.train||[],data.train_message||'Geen treinmeldingen beschikbaar.');renderTrafficItems($('trafficOv'),data.ov||[],data.ov_message||'Geen OV-meldingen beschikbaar.');}catch(e){renderTrafficItems($('trafficTrain'),[], 'Geen treinmeldingen beschikbaar.');renderTrafficItems($('trafficOv'),[], 'Geen OV-meldingen beschikbaar.');}}
})();
