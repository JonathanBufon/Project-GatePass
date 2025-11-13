(function(){
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.toast').forEach(function(el){ new bootstrap.Toast(el).show(); });
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el){ new bootstrap.Tooltip(el); });
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function(el){ new bootstrap.Popover(el); });
    document.querySelectorAll('a[href^="#"]').forEach(function(a){ a.addEventListener('click', function(e){ var t=document.querySelector(this.getAttribute('href')); if(!t)return; e.preventDefault(); t.scrollIntoView({behavior:'smooth'}); }); });
    var tipo=document.querySelector('[data-mask-selector="tipo"]'); var doc=document.querySelector('[data-mask="document"]');
    if(tipo&&doc){ var mask=function(){ var tp=tipo.value; var d=(doc.value||'').replace(/\D+/g,''); if(tp==='cpf'){ d=d.slice(0,11); doc.value=d.replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d{1,2})$/,'$1-$2'); doc.maxLength=14; doc.placeholder='000.000.000-00'; } else { d=d.slice(0,14); doc.value=d.replace(/(\d{2})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1/$2').replace(/(\d{4})(\d{1,2})$/,'$1-$2'); doc.maxLength=18; doc.placeholder='00.000.000/0001-00'; } }; tipo.addEventListener('change',mask); doc.addEventListener('input',mask); mask(); }

    var timerEl = document.querySelector('[data-expira-em]');
    if (timerEl) {
      var expiraIso = timerEl.getAttribute('data-expira-em');
      var expira = expiraIso ? new Date(expiraIso) : null;
      var form = document.querySelector('[data-checkout-form]');
      var submitBtn = document.querySelector('[data-checkout-submit]');
      var alertEl = document.getElementById('reserva-expirada-alert');
      var expired = false;
      var out = function(ms){ var s=Math.max(0, Math.floor(ms/1000)); var m=Math.floor(s/60); var r=s%60; return (m<10?'0':'')+m+':'+(r<10?'0':'')+r; };
      var onExpire = function(){
        if (expired) return;
        expired = true;
        timerEl.textContent = '00:00';
        timerEl.classList.add('text-warning');
        if (alertEl) alertEl.classList.remove('d-none');
        if (submitBtn) { submitBtn.setAttribute('disabled','disabled'); submitBtn.classList.add('disabled'); }
      };
      var tick = function(){
        if(!expira) return;
        var now = new Date();
        var diff = expira - now;
        if (diff <= 0) { onExpire(); return; }
        timerEl.textContent = out(diff);
      };
      if (form) {
        form.addEventListener('submit', function(e){ if (expired) { e.preventDefault(); e.stopPropagation(); } });
      }
      tick();
      setInterval(tick, 1000);
    }

    // Money mask: formats as dot-decimal (e.g., 1234 -> 12.34)
    document.querySelectorAll('[data-mask="money"]').forEach(function(inp){
      var format = function(){
        var raw = (inp.value||'').replace(/[^\d]/g,'');
        if(raw.length === 0){ inp.value=''; return; }
        var num = (parseInt(raw,10) || 0) / 100;
        inp.value = num.toFixed(2);
      };
      inp.addEventListener('input', format);
      // Initialize once on load
      format();
    });
  });
})();
