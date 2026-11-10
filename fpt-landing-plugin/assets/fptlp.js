(function(){
  'use strict';

  function initializeCarousel(carousel){
    if(carousel.dataset.fptlpReady === '1') return;
    carousel.dataset.fptlpReady = '1';
    var track = carousel.querySelector('.fptlp-grid');
    var prev = carousel.querySelector('.fptlp-prev');
    var next = carousel.querySelector('.fptlp-next');
    var panel = carousel.closest('.fptlp-tab-panel');
    var counter = panel && panel.querySelector('.fptlp-slide-count');
    if(!track || !prev || !next) return;

    function metrics(){
      var first = track.querySelector('.fptlp-card');
      if(!first) return null;
      var gap = parseFloat(getComputedStyle(track).columnGap) || 0;
      var step = first.getBoundingClientRect().width + gap;
      var total = track.querySelectorAll('.fptlp-card').length;
      var visible = step ? Math.min(total, Math.max(1, Math.round((track.clientWidth + gap) / step))) : 1;
      var firstVisible = Math.min(Math.max(1, Math.round(track.scrollLeft / (step || 1)) + 1), Math.max(1,total - visible + 1));
      return {step:step,total:total,visible:visible,first:firstVisible};
    }
    function update(){
      if(panel && panel.hidden) return;
      var max = Math.max(0, track.scrollWidth - track.clientWidth);
      var canScroll = max > 3;
      carousel.classList.toggle('fptlp-no-scroll', !canScroll);
      prev.disabled = !canScroll || track.scrollLeft <= 3;
      next.disabled = !canScroll || track.scrollLeft >= max-3;
      var m = metrics();
      if(counter && m){
        counter.textContent = 'Gói ' + m.first + '–' + Math.min(m.total,m.first+m.visible-1) + ' / ' + m.total;
      }
    }
    function move(direction){
      var m = metrics();
      if(!m) return;
      track.scrollBy({left:direction*m.step,behavior:window.matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth'});
    }
    prev.addEventListener('click',function(){move(-1);});
    next.addEventListener('click',function(){move(1);});
    track.addEventListener('scroll',update,{passive:true});
    carousel.addEventListener('fptlp:refresh',function(){requestAnimationFrame(update);});
    if(typeof ResizeObserver !== 'undefined') new ResizeObserver(update).observe(track);
    else window.addEventListener('resize',update,{passive:true});
    update();
  }

  function initializeTabs(root){
    var list = root.querySelector('.fptlp-package-tabs');
    if(!list) return;
    var tabs = Array.prototype.slice.call(list.querySelectorAll('[role="tab"]'));
    var panels = tabs.map(function(tab){return document.getElementById(tab.getAttribute('aria-controls'));});
    function centerTab(tab){
      if(!tab) return;
      var tabBox=tab.getBoundingClientRect();
      var listBox=list.getBoundingClientRect();
      var target=list.scrollLeft + (tabBox.left + tabBox.width/2) - (listBox.left + listBox.width/2);
      list.scrollTo({left:target,behavior:'auto'});
    }
    function activate(index, focusTab){
      tabs.forEach(function(tab,i){
        var active = index===i;
        tab.classList.toggle('is-active',active);
        tab.setAttribute('aria-selected',active?'true':'false');
        tab.tabIndex = active ? 0 : -1;
        if(panels[i]) panels[i].hidden = !active;
      });
      if(focusTab) tabs[index].focus();
      centerTab(tabs[index]);
      var activeCarousel = panels[index] && panels[index].querySelector('.fptlp-carousel');
      if(activeCarousel) activeCarousel.dispatchEvent(new Event('fptlp:refresh'));
    }
    // Resolve on the client so cached pages can serve different campaign links.
    var requested=root.dataset.campaignGroup || new URLSearchParams(window.location.search).get('fptlp_group');
    var requestedIndex=panels.findIndex(function(panel){return panel && panel.dataset.fptlpCategory===requested;});
    if(requestedIndex>=0) activate(requestedIndex,false);
    requestAnimationFrame(function(){
      centerTab(tabs.find(function(tab){return tab.getAttribute('aria-selected')==='true';}) || tabs[0]);
    });
    tabs.forEach(function(tab,index){
      tab.addEventListener('click',function(){activate(index,false);});
      tab.addEventListener('keydown',function(e){
        var destination;
        if(e.key==='ArrowRight') destination=(index+1)%tabs.length;
        else if(e.key==='ArrowLeft') destination=(index-1+tabs.length)%tabs.length;
        else if(e.key==='Home') destination=0;
        else if(e.key==='End') destination=tabs.length-1;
        else return;
        e.preventDefault(); activate(destination,true);
      });
    });
  }

  function initializePopup(root){
    var modal = root.querySelector('.fptlp-modal');
    if(!modal) return;
    var panel = modal.querySelector('.fptlp-modal-panel');
    var form = modal.querySelector('form');
    var packageInput = form.querySelector('[name=fptlp_package]');
    var kindInput = form.querySelector('[name=fptlp_kind]');
    var heading = modal.querySelector('[data-fptlp-modal-title]');
    var registrationHeading = heading.textContent;
    var phone = form.querySelector('[name=fptlp_phone]');
    var submitButton = form.querySelector('.fptlp-popup-submit');
    var submitting = false;
    function normalizedPhone(value){
      if(value.length>24 || !/^[0-9+() .-]+$/.test(value)) return '';
      var number=value.replace(/[() .-]/g,'').replace(/^(?:\+84|0084|84)/,'0');
      return /^(?:0[35789][0-9]{8}|02[0-9]{9})$/.test(number)?number:'';
    }
    var result=form.querySelector('.fptlp-form-result');
    var messages={success:'Đã nhận yêu cầu tư vấn. Nhân viên sẽ liên hệ để xác nhận nhu cầu, giá và lịch lắp đặt.',duplicate:'Yêu cầu này đã được ghi nhận trước đó. Bạn không cần gửi lại.',invalid_phone:'Số điện thoại chưa hợp lệ. Vui lòng kiểm tra lại.',expired:'Phiên đăng ký đã hết hạn. Vui lòng tải lại trang rồi thử lại.',rate_limited:'Bạn đã gửi nhiều yêu cầu. Vui lòng thử lại sau 10 phút hoặc liên hệ qua hotline.',busy:'Yêu cầu đang được xử lý. Vui lòng chờ một chút rồi thử lại.',error:'Chưa xác nhận được kết quả gửi. Thông tin vẫn được giữ lại; bạn có thể thử lại.'};
    function showResult(status){
      result.hidden=false;result.textContent=messages[status]||messages.error;
      var ok=status==='success'||status==='duplicate';
      result.classList.toggle('is-error',!ok);result.setAttribute('role',ok?'status':'alert');
      form.classList.toggle('is-complete',ok);
      if(ok) heading.textContent='Yêu cầu đã được ghi nhận';
      result.focus();
    }
    function recordConversion(id){
      if(!id || typeof id!=='string' || !/^[a-zA-Z0-9-]{1,80}$/.test(id)) return;
      window.fptlpSentConversions=window.fptlpSentConversions||new Set();
      if(window.fptlpSentConversions.has(id))return;
      try {if(sessionStorage.getItem('fptlp-conversion-'+id))return;sessionStorage.setItem('fptlp-conversion-'+id,'1');}catch(_){}
      window.fptlpSentConversions.add(id);
      window.dataLayer=window.dataLayer||[];
      // No name, phone, address or free-text field goes to analytics.
      window.dataLayer.push({event:'fptlp_lead_saved',fptlp_conversion_id:id});
      var sendTo=root.dataset.adsSendTo;
      if(sendTo && /^AW-[0-9]+\/[A-Za-z0-9_-]+$/.test(sendTo) && typeof window.gtag==='function') window.gtag('event','conversion',{send_to:sendTo,transaction_id:id});
    }
    phone.addEventListener('input',function(){phone.setCustomValidity('');});
    form.addEventListener('submit',function(event){
      if(submitting){event.preventDefault();return;}
      if(event.defaultPrevented) return;
      var number=normalizedPhone(phone.value);
      if(!number){
        event.preventDefault();
        phone.setCustomValidity('Vui lòng nhập số di động hoặc số cố định Việt Nam hợp lệ; có thể dùng +84.');
        phone.reportValidity();return;
      }
      phone.value=number;
      if(window.fetch && result){
        event.preventDefault();
        var data=new FormData(form);data.set('fptlp_ajax','1');
        result.hidden=true;submitting=true;submitButton.disabled=true;submitButton.textContent='Đang gửi…';form.setAttribute('aria-busy','true');
        var controller=typeof AbortController!=='undefined'?new AbortController():null;
        var timeout=setTimeout(function(){if(controller)controller.abort();},25000);
        fetch(form.getAttribute('action'),{method:'POST',body:data,credentials:'same-origin',signal:controller?controller.signal:undefined,headers:{Accept:'application/json'}})
          .then(function(response){if(!response.ok)throw new Error('HTTP');return response.json();})
          .then(function(payload){
            var status=payload&&Object.prototype.hasOwnProperty.call(messages,payload.status)?payload.status:'error';
            showResult(status);
            if(status==='success'){try{recordConversion(payload.conversion_id);}catch(_){}form.reset();}
          }).catch(function(){showResult('error');})
          .finally(function(){clearTimeout(timeout);submitting=false;submitButton.disabled=false;form.removeAttribute('aria-busy');submitButton.textContent=kindInput.value==='callback'?'Yêu cầu gọi lại ›':'Đăng ký nhanh ›';});
        return;
      }
      submitting=true;
      submitButton.disabled=true;
      submitButton.textContent='Đang gửi…';
      form.setAttribute('aria-busy','true');
    });
    window.addEventListener('pageshow',function(){
      submitting=false;submitButton.disabled=false;form.removeAttribute('aria-busy');
      submitButton.textContent=kindInput.value==='callback'?'Yêu cầu gọi lại ›':'Đăng ký nhanh ›';
    });
    var lastFocus = null;
    var previousOverflow = '';
    function open(kind,pkg){
      lastFocus = document.activeElement;
      previousOverflow = document.body.style.overflow;
      modal.hidden=false;
      if(result){result.hidden=true;result.textContent='';form.classList.remove('is-complete');}
      var callback = kind === 'callback';
      modal.classList.toggle('is-callback',callback);
      kindInput.value=callback?'callback':'register';
      packageInput.value=pkg||'';
      heading.textContent=callback?'Yêu cầu gọi lại':registrationHeading;
      var extra=form.querySelector('.fptlp-register-extra');
      if(extra){
        extra.hidden=callback;
        extra.querySelectorAll('input,select,textarea').forEach(function(field){field.disabled=callback;});
      }
      if(!submitting) form.querySelector('.fptlp-popup-submit').textContent=callback?'Yêu cầu gọi lại ›':'Đăng ký nhanh ›';
      document.body.style.overflow='hidden';
      setTimeout(function(){if(!modal.hidden && phone) phone.focus();},50);
    }
    function close(){
      modal.hidden=true;
      document.body.style.overflow=previousOverflow;
      if(lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
    }
    root.addEventListener('click',function(e){
      var trigger = e.target.closest('.fptlp-popup-open');
      if(trigger){e.preventDefault();open(trigger.dataset.modal||'register',trigger.dataset.package||'');}
      if(e.target.closest('[data-close-modal]')) close();
      var expand=e.target.closest('.fptlp-expand');
      if(expand){
        var card=expand.closest('.fptlp-card');
        if(!card) return;
        var currentlyExpanded = expand.getAttribute('aria-expanded')==='true';
        card.querySelectorAll('.fptlp-benefit-extra').forEach(function(el){el.hidden=currentlyExpanded;});
        expand.setAttribute('aria-expanded',currentlyExpanded?'false':'true');
        expand.textContent=currentlyExpanded?'Xem chi tiết':'Thu gọn';
      }
    });
    document.addEventListener('keydown',function(e){
      if(modal.hidden) return;
      if(e.key === 'Escape'){e.preventDefault();close();return;}
      if(e.key!=='Tab') return;
      var focusable=Array.prototype.slice.call(panel.querySelectorAll('button:not([disabled]),input:not([disabled]),select:not([disabled]),a[href],[tabindex="-1"]')).filter(function(el){return el.offsetParent!==null && !el.closest('[hidden]');});
      if(!focusable.length) return;
      var first=focusable[0],last=focusable[focusable.length-1];
      if(e.shiftKey && document.activeElement===first){e.preventDefault();last.focus();}
      else if(!e.shiftKey && document.activeElement===last){e.preventDefault();first.focus();}
    });
  }

  function ready(){
    document.querySelectorAll('.fptlp').forEach(function(root){
      if(root.dataset.fptlpInitialized === '1') return;
      root.dataset.fptlpInitialized = '1';
      root.querySelectorAll('.fptlp-carousel').forEach(initializeCarousel);
      initializeTabs(root);
      initializePopup(root);
    });
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',ready);
  else ready();
})();

(function(){
  function init(){document.querySelectorAll('.fptlp-banner-slider').forEach(function(root){
    if(root.dataset.sliderReady)return;root.dataset.sliderReady='1';
    var slides=Array.from(root.querySelectorAll('.fptlp-banner-slide')),dots=Array.from(root.querySelectorAll('.fptlp-banner-dots button')),current=0;
    if(slides.length<2)return;
    function show(index){current=(index+slides.length)%slides.length;slides.forEach(function(s,i){s.hidden=i!==current;s.setAttribute('aria-hidden',String(i!==current));});dots.forEach(function(d,i){d.setAttribute('aria-pressed',String(i===current));});}
    root.querySelector('.fptlp-banner-prev').addEventListener('click',function(){show(current-1);});
    root.querySelector('.fptlp-banner-next').addEventListener('click',function(){show(current+1);});
    dots.forEach(function(d,i){d.addEventListener('click',function(){show(i);});});
    root.addEventListener('keydown',function(e){if(e.key==='ArrowLeft'||e.key==='ArrowRight'){e.preventDefault();show(current+(e.key==='ArrowLeft'?-1:1));}});
    var x=null,y=null;
    root.addEventListener('touchstart',function(e){x=e.touches[0].clientX;y=e.touches[0].clientY;},{passive:true});
    root.addEventListener('touchend',function(e){if(x===null)return;var dx=e.changedTouches[0].clientX-x,dy=e.changedTouches[0].clientY-y;if(Math.abs(dx)>45&&Math.abs(dx)>Math.abs(dy))show(current+(dx<0?1:-1));x=null;},{passive:true});show(0);
  });}
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
