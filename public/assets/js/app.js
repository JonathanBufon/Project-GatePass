(function(){
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.toast').forEach(function(el){ new bootstrap.Toast(el).show(); });
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el){ new bootstrap.Tooltip(el); });
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function(el){ new bootstrap.Popover(el); });
    document.querySelectorAll('a[href^="#"]').forEach(function(a){ a.addEventListener('click', function(e){ var t=document.querySelector(this.getAttribute('href')); if(!t)return; e.preventDefault(); t.scrollIntoView({behavior:'smooth'}); }); });
    var tipo=document.querySelector('[data-mask-selector="tipo"]'); var doc=document.querySelector('[data-mask="document"]');
    if(tipo&&doc){ var mask=function(){ var tp=tipo.value; var d=(doc.value||'').replace(/\D+/g,''); if(tp==='cpf'){ d=d.slice(0,11); doc.value=d.replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d{1,2})$/,'$1-$2'); doc.maxLength=14; doc.placeholder='000.000.000-00'; } else { d=d.slice(0,14); doc.value=d.replace(/(\d{2})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1/$2').replace(/(\d{4})(\d{1,2})$/,'$1-$2'); doc.maxLength=18; doc.placeholder='00.000.000/0001-00'; } }; tipo.addEventListener('change',mask); doc.addEventListener('input',mask); mask(); }
  });
})();
