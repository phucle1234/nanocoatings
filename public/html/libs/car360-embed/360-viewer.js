
/*! 360-Viewer (vanilla) v0.2 — drop-in component
   Usage:
   <div class="spin360"
        data-total="36"
        data-template="https://example.com/car_{i}.jpg"
        data-autoplay="true"
        data-speed="0.6"
        style="width:100%;max-width:1100px;aspect-ratio:16/9"></div>
*/
(function(){
  function make(el){
    const total = parseInt(el.dataset.total || "36", 10);
    const autoplay = (el.dataset.autoplay || "true") === "true";
    const speed = parseFloat(el.dataset.speed || "0.2");
    const template = el.dataset.template; // e.g. https://.../car_{i}.jpg
    const pad = parseInt(el.dataset.pad || "0", 10);
    const folder = el.dataset.folder || "";
    const prefix = el.dataset.prefix || "frame_";
    const ext = el.dataset.ext || "jpg";

    function url(i){
      if (template) return template.replace("{i}", i);
      const s = String(i).padStart(pad, "0");
      const slash = folder && !folder.endsWith("/") ? "/" : "";
      return folder + slash + prefix + s + "." + ext;
    }

    el.classList.add("spin360-wrap");
    el.innerHTML = `
      <div class="spin360-stage">
        <img class="spin360-img" alt="360 frame" draggable="false"/>
        <div class="spin360-progress"><div class="spin360-bar"></div></div>
        <div class="spin360-hud">
          <button class="spin360-btn" data-action="play">⏯</button>
          <input class="spin360-range" type="range" min="0" max="2" step="0.01" value="${speed}"/>
          <button class="spin360-btn" data-action="reset">↺</button>
        </div>
      </div>`;

    const stage = el.querySelector(".spin360-stage");
    const img = el.querySelector(".spin360-img");
    const bar = el.querySelector(".spin360-bar");
    const progress = el.querySelector(".spin360-progress");
    const range = el.querySelector(".spin360-range");
    const playBtn = el.querySelector('[data-action="play"]');
    const resetBtn = el.querySelector('[data-action="reset"]');

    // preload
    const frames = new Array(total);
    let loaded = 0;
    for (let i=1;i<=total;i++){
      const im = new Image();
      im.src = url(i);
      im.onload = im.onerror = () => {
        loaded++; bar.style.width = (100*loaded/total).toFixed(1)+'%';
        if (loaded===total){ progress.style.display='none'; render(); }
      };
      frames[i-1] = im;
    }

    let idx = 0;
    let playing = autoplay;
    let spd = parseFloat(range.value || speed);
    function render(){ img.src = frames[Math.floor(idx % total)].src; }

    function loop(){
      if (playing){ idx = (idx + Math.max(0.01, spd)) % total; render(); }
      requestAnimationFrame(loop);
    }
    loop();

    // drag
    let dragging=false, lastX=0, startIdx=0;
    const down = (e)=>{ dragging=true; lastX=(e.touches?e.touches[0].clientX:e.clientX); startIdx=idx; playing=false; };
    const move = (e)=>{
      if(!dragging) return;
      const x = (e.touches?e.touches[0].clientX:e.clientX);
      const dx = x-lastX; lastX=x;
      const w = stage.getBoundingClientRect().width || 800;
      idx = (startIdx + (dx / w) * total) % total; if (idx<0) idx += total; render();
    };
    const up = ()=>{ dragging=false; };

    stage.addEventListener('mousedown', down);
    stage.addEventListener('mousemove', move);
    window.addEventListener('mouseup', up);
    stage.addEventListener('touchstart', down, {passive:true});
    stage.addEventListener('touchmove', move, {passive:true});
    window.addEventListener('touchend', up);

    // ui
    playBtn.addEventListener('click', ()=>{ playing=!playing; });
    resetBtn.addEventListener('click', ()=>{ idx=0; render(); });
    range.addEventListener('input', ()=>{ spd=parseFloat(range.value); });
  }

  // styles (scoped via class names)
  const css = `
  .spin360-wrap{position:relative;display:block}
  .spin360-stage{position:relative;width:100%;height:100%;border-radius:18px;background:#0f1114;box-shadow:0 40px 90px rgba(0,0,0,.6),0 0 0 1px rgba(255,255,255,.06) inset;overflow:hidden}
  .spin360-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .spin360-progress{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:220px;height:6px;background:rgba(255,255,255,.1);border-radius:999px;overflow:hidden}
  .spin360-bar{height:100%;width:0;background:linear-gradient(90deg,#7be3ff,#5ad3ff)}
  .spin360-hud{position:absolute;right:12px;top:12px;display:flex;gap:8px;align-items:center;z-index:3}
  .spin360-btn{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.16);color:#e9eef5;padding:6px 9px;border-radius:10px;font-size:13px;cursor:pointer;backdrop-filter:blur(4px)}
  .spin360-range{accent-color:#5ad3ff;width:120px}
  `;
  const style = document.createElement('style'); style.textContent = css; document.head.appendChild(style);

  // init all on DOM ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", ()=>{ document.querySelectorAll(".spin360").forEach(make); });
  } else {
    document.querySelectorAll(".spin360").forEach(make);
  }
})();
