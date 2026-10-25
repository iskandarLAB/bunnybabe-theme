/* Bunny Babe — TikTok-style shopping flow: product sheet, fly-to-bag, bag sheet, order summary. */
(function ($) {
	'use strict';
	var D = window.bbData || {};
	var busy = false;
	var state = { empty: !document.body.classList.contains('bb-has-bag') };
	var ps = { key: null, qty: 1 };
	var lastFocus = null;
	var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	function track(ev, d) { try { if (window.bbTrack) { window.bbTrack(ev, d); } } catch (e) {} }
	function money(html) { var n = parseFloat(String(html || '').replace(/<[^>]+>/g, '').replace(/[^\d.]/g, '')); return isNaN(n) ? 0 : n; }

	function post(data) {
		return $.post(D.ajax, $.extend({ action: 'bb_bag' }, data));
	}

	/* ---------- Render bag state everywhere ---------- */
	function render(s) {
		if (!s) { return; }
		state = s;
		$('[data-bb="count"]').text(s.count);
		$('.bb-dot[data-bb="count"]').prop('hidden', !s.count);
		$('[data-bb="total"]').html(s.total_html);
		$('[data-bb="items"]').html(s.items_html);
		$('[data-bb="tier"]').text(s.tier_msg);
		$('[data-bb="tier-bar"]').css('width', s.tier_pct + '%');
		$('[data-bb="notices"]').html(s.notices || '');
		$('[data-bb="bag-empty"]').prop('hidden', !s.empty);
		$('[data-bb="bag-full"]').prop('hidden', !!s.empty);
		$('[data-bb-checkout]').prop('disabled', !!s.empty);

		var $fb = $('[data-bb="freebar"]');
		$fb.toggleClass('is-free', !!s.free_ship).prop('hidden', !!s.empty)
			.text(s.free_ship ? '✓ You\'ve got FREE shipping!' : '🚚 Add 1 more: 2 for RM59 + FREE shipping (just RM15 more)');
		$('[data-bb="co-upsell"]').prop('hidden', s.count !== 1);

		$('[data-bb="saving-line"]').text(s.saving_amt ? 'Saving ' + s.saving_amt : '').prop('hidden', !s.saving_amt);
		$('[data-bb="saving-banner"]').text(s.saving_amt ? 'Congrats! You\'re saving ' + s.saving_amt + ' on this order ♡' : '').prop('hidden', !s.saving_amt);

		$('body').toggleClass('bb-has-bag', !s.empty);

		$('[data-bb-card]').each(function () {
			var id = String($(this).data('pid'));
			var q = (s.qtys && s.qtys[id]) ? s.qtys[id] : 0;
			$(this).find('[data-bb="lbl"]').text(q ? q + ' in bag · add more' : 'Add to bag');
		});

		var $b = $('[data-bb="buy-main"]');
		if (s.empty) {
			$b.attr('data-mode', 'buy').html('<strong>Buy now</strong><small>RM39 · FREE shipping 2+</small>');
		} else {
			$b.attr('data-mode', 'checkout').html('<strong>Checkout (' + s.count + ')</strong><small>' + s.total_html + (s.saving_amt ? ' · saving ' + s.saving_amt : '') + '</small>');
		}

		// Refresh WooCommerce's order review (totals, shipping, payment).
		$(document.body).trigger('update_checkout');
	}

	function act(data, $btn) {
		if (busy) { return $.Deferred().reject().promise(); }
		busy = true;
		if ($btn) { $btn.addClass('is-loading').attr('aria-busy', 'true'); }
		return post(data).done(function (r) {
			if (r && r.success) { render(r.data); }
		}).always(function () {
			busy = false;
			if ($btn) { $btn.removeClass('is-loading').removeAttr('aria-busy'); }
		});
	}

	/* ---------- Sheets ---------- */
	function anyOpen() {
		return document.querySelector('.bb-sheet:not([hidden]), .bb-co:not([hidden])');
	}
	function lock() { document.body.classList.add('bb-lock'); }
	function unlockIfIdle() { if (!anyOpen()) { document.body.classList.remove('bb-lock'); } }

	function openSheet(id) {
		var el = document.getElementById(id);
		if (!el) { return; }
		if (el.hidden) { lastFocus = document.activeElement; }
		el.hidden = false;
		lock();
		var f = el.querySelector('[data-bb-focus]') || el.querySelector('.bb-sheet__x');
		if (f) { f.focus({ preventScroll: true }); }
	}
	function closeSheet(el) {
		if (!el || el.hidden) { return; }
		el.hidden = true;
		unlockIfIdle();
		if (!anyOpen() && lastFocus && lastFocus.focus) { lastFocus.focus({ preventScroll: true }); }
	}
	function closeAllSheets() {
		$('.bb-sheet').each(function () { this.hidden = true; });
	}

	$(document).on('click', '[data-bb-close]', function () { closeSheet($(this).closest('.bb-sheet')[0]); });
	$(document).on('keydown', function (e) {
		if (e.key !== 'Escape') { return; }
		var co = document.querySelector('.bb-co:not([hidden])');
		if (co) { closeCheckout(); return; }
		var open = $('.bb-sheet:not([hidden])').last()[0];
		if (open) { closeSheet(open); }
	});

	/* ---------- Product sheet ---------- */
	function pickScent(key) {
		var $t = $('[data-bb-pick="' + key + '"]');
		if (!$t.length) { $t = $('[data-bb-pick]').first(); }
		ps.key = $t.data('bb-pick');
		$('[data-bb-pick]').attr('aria-pressed', 'false');
		$t.attr('aria-pressed', 'true');
		$('[data-bb="ps-img"]').attr('src', $t.data('img')).attr('alt', 'Bunny Babe ' + $t.data('name'));
		$('[data-bb="ps-name"]').text($t.data('name') + ' · ' + $t.data('mood'));
	}
	function setQty(q) {
		ps.qty = Math.max(1, Math.min(20, parseInt(q, 10) || 1));
		$('[data-bb="ps-qty"]').text(ps.qty);
	}

	$(document).on('click', '[data-bb-open]', function (e) {
		e.preventDefault();
		closeAllSheets();
		pickScent($(this).data('bb-open') || ps.key);
		setQty($(this).data('qty') || 1);
		openSheet('bb-product');
		track('ViewContent', { value: 39, ids: [String($('[data-bb-pick="' + ps.key + '"]').data('pid'))], qty: 1 });
	});
	$(document).on('click', '[data-bb-pick]', function () { pickScent($(this).data('bb-pick')); });
	$(document).on('click', '[data-bb="ps-minus"]', function () { setQty(ps.qty - 1); });
	$(document).on('click', '[data-bb="ps-plus"]', function () { setQty(ps.qty + 1); });

	function addFromSheet(buyNow) {
		var $t = $('[data-bb-pick="' + ps.key + '"]');
		var pid = $t.data('pid');
		if (!pid) { toast('This scent is not available yet.'); return; }
		var img = document.querySelector('[data-bb="ps-img"]');
		var $btns = $('[data-bb="ps-add"], [data-bb="ps-buy"]');
		act({ op: 'add', product: pid, qty: ps.qty }, $btns).done(function (r) {
			if (!r || !r.success) { return; }
			track('AddToCart', { value: Math.round(39 * ps.qty * 100) / 100, ids: [String(pid)], qty: ps.qty });
			var rect = img ? img.getBoundingClientRect() : null;
			closeSheet(document.getElementById('bb-product'));
			if (buyNow) {
				openCheckout();
			} else {
				fly(img, rect);
				toast('Added to bag ✓' + (r.data.saving_amt ? ' · saving ' + r.data.saving_amt : ''));
			}
		});
	}
	$(document).on('click', '[data-bb="ps-add"]', function () { addFromSheet(false); });
	$(document).on('click', '[data-bb="ps-buy"]', function () { addFromSheet(true); });

	/* ---------- Fly-to-bag + toast ---------- */
	function bump() {
		$('[data-bb="bag-target"], .bb-bagbtn').each(function () {
			this.classList.remove('is-bump');
			void this.offsetWidth;
			this.classList.add('is-bump');
		});
	}
	function fly(img, a) {
		var target = document.querySelector('[data-bb="bag-target"]');
		if (!img || !a || !target || reduceMotion) { bump(); return; }
		var b = target.getBoundingClientRect();
		if (!b.width) { bump(); return; }
		var c = img.cloneNode(false);
		c.removeAttribute('data-bb');
		c.className = 'bb-fly';
		c.style.left = a.left + 'px';
		c.style.top = a.top + 'px';
		c.style.width = a.width + 'px';
		c.style.height = a.height + 'px';
		document.body.appendChild(c);
		requestAnimationFrame(function () {
			requestAnimationFrame(function () {
				var dx = (b.left + b.width / 2) - (a.left + a.width / 2);
				var dy = (b.top + b.height / 2) - (a.top + a.height / 2);
				c.style.transform = 'translate(' + dx + 'px,' + dy + 'px) scale(.18) rotate(-12deg)';
				c.style.opacity = '.3';
			});
		});
		setTimeout(function () { c.remove(); bump(); }, 750);
	}
	var toastTimer;
	function toast(msg) {
		var $t = $('[data-bb="toast"]');
		$t.text(msg).addClass('is-on');
		clearTimeout(toastTimer);
		toastTimer = setTimeout(function () { $t.removeClass('is-on'); }, 2400);
	}

	/* ---------- Bag sheet ---------- */
	$(document).on('click', '[data-bb-openbag]', function (e) { e.preventDefault(); closeAllSheets(); openSheet('bb-bag'); });
	$(document).on('click', '[data-bb="buy-main"]', function () {
		if ($(this).attr('data-mode') === 'checkout') {
			openCheckout();
		} else {
			pickScent(ps.key);
			setQty(1);
			openSheet('bb-product');
		}
	});
	$(document).on('click', '[data-bb-set]', function () {
		act({ op: 'set', product: $(this).data('bb-set'), qty: $(this).data('qty') }, $(this));
	});
	$(document).on('click', '[data-bb-addall]', function () {
		act({ op: 'addall' }, $(this)).done(function (r) {
			if (r && r.success) {
				track('AddToCart', { value: 99, ids: $('[data-bb-pick]').map(function () { return String($(this).data('pid')); }).get(), qty: 5 });
				toast('All 5 scents added ✓' + (r.data.saving_amt ? ' · saving ' + r.data.saving_amt : ''));
				closeAllSheets();
				openSheet('bb-bag');
			}
		});
	});
	$(document).on('click', '[data-bb="apply-coupon"]', function () {
		var code = $.trim($('#bb-coupon').val());
		if (code) { act({ op: 'coupon', code: code }, $(this)); }
	});
	$(document).on('keydown', '#bb-coupon', function (e) {
		if (e.key === 'Enter') { e.preventDefault(); $('[data-bb="apply-coupon"]').trigger('click'); }
	});

	/* ---------- Checkout (order summary) ---------- */
	function openCheckout() {
		var co = document.getElementById('checkout');
		if (!co) { return; }
		if (state && state.empty) { openSheet('bb-bag'); return; }
		closeAllSheets();
		co.hidden = false;
		lock();
		track('InitiateCheckout', { value: money(state.total_html), ids: [], qty: state.count || 1 });
		if (restored > 1 && !restoredToast) {
			restoredToast = true;
			setTimeout(function () { toast('Welcome back! Your details are filled in ✓'); }, 450);
		}
		$(document.body).trigger('update_checkout');
		var body = co.querySelector('.bb-co__body');
		if (body) { body.scrollTop = 0; }
		var back = co.querySelector('[data-bb-backbag]');
		if (back) { back.focus({ preventScroll: true }); }
	}
	function closeCheckout() {
		var co = document.getElementById('checkout');
		if (co) { co.hidden = true; }
		unlockIfIdle();
	}
	$(document).on('click', '[data-bb-checkout]', function () { if (!state.empty) { openCheckout(); } });
	$(document).on('click', '[data-bb-backbag]', function () { closeCheckout(); openSheet('bb-bag'); });
	$(document).on('click', '[data-bb="place"]', function () {
		var p = document.getElementById('place_order');
		if (p) { p.click(); }
	});
	$(document.body).on('checkout_error', function () {
		var body = document.querySelector('.bb-co__body');
		if (body) { body.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' }); }
	});

	// Old #checkout / #bag links open the bag instead of jumping.
	$(document).on('click', 'a[href="#checkout"], a[href="#bag"]', function (e) {
		if (!document.getElementById('bb-bag')) { return; }
		e.preventDefault();
		closeAllSheets();
		openSheet('bb-bag');
	});

	/* ---------- Voucher claim ---------- */
	$(document).on('submit', '[data-bb="claim-form"]', function (e) {
		e.preventDefault();
		var $f = $(this);
		var $err = $('[data-bb="claim-error"]');
		var phone = $.trim($f.find('[name="phone"]').val());
		if (phone.replace(/\D/g, '').length < 9) {
			$err.text('Enter a valid phone number to claim.').prop('hidden', false);
			return;
		}
		$err.prop('hidden', true);
		var $btn = $f.find('button[type="submit"]').prop('disabled', true);
		$.post(D.ajax, { action: 'bb_claim', phone: phone, website: $f.find('[name="website"]').val() })
			.done(function (r) {
				if (r && r.success) {
					$f.prop('hidden', true);
					$('[data-bb="claim-done"]').prop('hidden', false);
					if (r.data && r.data.bag) { render(r.data.bag); }
					var st = lsGet();
					if (!st.billing_phone) { st.billing_phone = phone; lsSet(st); }
					if (!$.trim(fieldEl('billing_phone').val() || '')) { setField('billing_phone', phone); }
					toast('RM5 voucher claimed ✓');
				} else {
					$err.text((r && r.data && r.data.message) || 'Something went wrong. Please try again.').prop('hidden', false);
				}
			})
			.fail(function () { $err.text('Something went wrong. Please try again.').prop('hidden', false); })
			.always(function () { $btn.prop('disabled', false); });
	});
	$(document).on('click', '[data-bb="copy"]', function () {
		var $b = $(this);
		var code = String($b.data('code'));
		var done = function () { $b.text('Copied!'); };
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(code).then(done, done);
		} else { done(); }
	});

	/* ---------- Checkout autofill + capture ---------- */
	var AF_KEY = 'bb_details';
	var AF_FIELDS = ['billing_first_name', 'billing_phone', 'billing_email', 'billing_address_1', 'billing_postcode', 'billing_city', 'billing_state'];
	var restored = 0, restoredToast = false, capTimer = null, pcData = null, pcReq = null;
	function lsGet() { try { return JSON.parse(window.localStorage.getItem(AF_KEY) || '{}') || {}; } catch (e) { return {}; } }
	function lsSet(o) { try { window.localStorage.setItem(AF_KEY, JSON.stringify(o)); } catch (e) {} }
	function fieldEl(name) { return $('.bb-co-form [name="' + name + '"]'); }
	function setField(name, val) {
		var $i = fieldEl(name);
		if (!$i.length || !val) { return false; }
		if ($i.is('select')) {
			if (!$i.find('option[value="' + val + '"]').length) { return false; }
			$i.val(val).trigger('change');
		} else {
			$i.val(val).trigger('input');
		}
		var $row = $i.closest('.form-row').removeClass('bb-filled');
		void $row[0].offsetWidth;
		$row.addClass('bb-filled');
		return true;
	}
	function restoreDetails() {
		var d = lsGet();
		AF_FIELDS.forEach(function (f) {
			var $i = fieldEl(f);
			if ($i.length && !$.trim($i.val() || '') && d[f] && setField(f, d[f])) { restored++; }
		});
		if (restored) { $(document.body).trigger('update_checkout'); }
	}
	function saveLocal() {
		if (usingSaved()) { return; }
		var store = lsGet();
		AF_FIELDS.forEach(function (f) {
			var v = $.trim(fieldEl(f).val() || '');
			if (v) { store[f] = v; }
		});
		lsSet(store);
	}
	function capture() {
		var phone = $.trim(fieldEl('billing_phone').val() || '');
		if (phone.replace(/\D/g, '').length < 9) { return; }
		var data = { action: 'bb_capture', phone: phone };
		AF_FIELDS.forEach(function (f) { data[f] = $.trim(fieldEl(f).val() || ''); });
		$.post(D.ajax, data);
	}
	$(document).on('change', '.bb-co-form input, .bb-co-form select', function () {
		saveLocal();
		clearTimeout(capTimer);
		capTimer = setTimeout(capture, 600);
	});

	/* Postcode → City + State (Malaysia) */
	function loadPostcodes() {
		if (!pcReq) { pcReq = $.getJSON(D.ajax, { action: 'bb_postcodes' }).done(function (d) { pcData = d; }); }
		return pcReq;
	}
	$(document).on('input change', '.bb-co-form [name="billing_postcode"]', function () {
		var v = String($(this).val() || '').replace(/\D/g, '');
		var $row = $(this).closest('.form-row');
		$row.find('.bb-pchint').remove();
		if (v.length !== 5) { return; }
		loadPostcodes().done(function (d) {
			var hit = d && d.p && d.p[v];
			if (!hit) {
				$row.append('<span class="bb-pchint">Postcode not found. Fill in City & State yourself.</span>');
				return;
			}
			var cityOk = setField('billing_city', d.c[hit[0]]);
			var stateOk = setField('billing_state', d.s[hit[1]]);
			if (cityOk || stateOk) {
				saveLocal();
				$(document.body).trigger('update_checkout');
			}
		});
	});
	$(document).on('focus', '.bb-co-form [name="billing_postcode"]', function () { loadPostcodes(); });

	/* ---------- Express checkout ---------- */
	function phoneTop() {
		var $p = $('#billing_phone_field');
		var $w = $p.parent();
		if ($p.length && $w.children().first()[0] !== $p[0]) { $p.prependTo($w); }
		var lbl = $p.find('label')[0];
		if (lbl && lbl.firstChild && lbl.firstChild.nodeType === 3 && !/WhatsApp/.test(lbl.firstChild.nodeValue)) { lbl.firstChild.nodeValue = 'WhatsApp number '; }
		$('#bb-saved').insertAfter($p);
	}
	$(document.body).on('updated_checkout country_to_state_changed init_checkout', phoneTop);

	/* Saved address (any device): masked preview, real values stay on the server. */
	var SAVED_ROWS = '#billing_first_name_field, #billing_address_1_field, #billing_postcode_field, #billing_city_field, #billing_state_field';
	var lookedUp = '';
	function usingSaved() { return $('form.checkout input[name="bb_use_saved"]').length > 0; }
	function esc(t) { return $('<div>').text(t == null ? '' : String(t)).html(); }
	function useSaved(d) {
		var $f = $('form.checkout');
		if (!$f.find('input[name="bb_use_saved"]').length) { $f.append('<input type="hidden" name="bb_use_saved" value="1">'); }
		$(SAVED_ROWS).addClass('bb-hide-saved');
		fieldEl('billing_postcode').val(d.postcode);
		fieldEl('billing_city').val(d.city);
		fieldEl('billing_state').val(d.state).trigger('change');
		$('#bb-saved').addClass('is-used').find('[data-bb="saved-q"]').text('Shipping to your last address ✓');
		$('#bb-saved [data-bb="saved-yes"]').prop('hidden', true);
		$('#bb-saved [data-bb="saved-no"]').text('Change');
		$(document.body).trigger('update_checkout');
	}
	function dropSaved() {
		$('form.checkout input[name="bb_use_saved"]').remove();
		$(SAVED_ROWS).removeClass('bb-hide-saved');
		$('#bb-saved').remove();
	}
	function lookupSaved() {
		var phone = $.trim(fieldEl('billing_phone').val() || '');
		var digits = phone.replace(/\D/g, '');
		if (digits.length < 9 || digits === lookedUp || usingSaved()) { return; }
		if ($.trim(fieldEl('billing_address_1').val() || '')) { return; }
		lookedUp = digits;
		$.post(D.ajax, { action: 'bb_lookup', phone: phone }).done(function (r) {
			if (!r || !r.success || !r.data.found) { return; }
			var d = r.data;
			$('#bb-saved').remove();
			var $c = $('<div id="bb-saved" class="bb-saved" role="group" aria-label="Saved address">' +
				'<strong data-bb="saved-q">Welcome back! Ship to your last address?</strong>' +
				'<span>' + esc(d.name) + ' · ' + esc(d.address) + ', ' + esc(d.postcode) + ' ' + esc(d.city) + ', ' + esc(d.state_name) + '</span>' +
				'<div class="bb-saved__btns"><button type="button" class="bb-btn bb-btn--black bb-btn--sm" data-bb="saved-yes">Use this address</button>' +
				'<button type="button" class="bb-btn bb-btn--white bb-btn--sm" data-bb="saved-no">Use a new one</button></div></div>');
			$c.data('saved', d);
			$c.insertAfter('#billing_phone_field');
		});
	}
	$(document).on('change', '.bb-co-form [name="billing_phone"]', lookupSaved);
	$(document).on('click', '#bb-saved [data-bb="saved-yes"]', function () { useSaved($('#bb-saved').data('saved')); });
	$(document).on('click', '#bb-saved [data-bb="saved-no"]', function () {
		dropSaved();
		fieldEl('billing_first_name').trigger('focus');
	});

	/* Remember payment method + bank/e-wallet on this device. */
	var PAY_KEY = 'bb_pay';
	function payGet() { try { return JSON.parse(window.localStorage.getItem(PAY_KEY) || '{}') || {}; } catch (e) { return {}; } }
	$(document).on('change', '.bb-co-form input[name="payment_method"], .bb-co-form select[name^="chip_payment_method"]', function () {
		var p = payGet();
		p[this.name] = $(this).val();
		try { window.localStorage.setItem(PAY_KEY, JSON.stringify(p)); } catch (e) {}
	});
	$(document.body).on('updated_checkout', function () {
		var p = payGet();
		if (p.payment_method) {
			var $r = $('.bb-co-form input[name="payment_method"][value="' + p.payment_method + '"]');
			if ($r.length && !$r.prop('checked')) { $r.prop('checked', true).trigger('click'); }
		}
		$('.bb-co-form select[name^="chip_payment_method"]').each(function () {
			var $s = $(this), v = p[this.name];
			if (v && !$s.val() && $s.find('option[value="' + v + '"]').length) {
				$s.val(v).trigger('change');
			}
		});
	});

	/* ---------- On load ---------- */
	$(function () {
		pickScent($('[data-bb-pick]').first().data('bb-pick'));
		fieldEl('billing_postcode').attr({ inputmode: 'numeric', maxlength: 5, autocomplete: 'postal-code' });
		fieldEl('billing_phone').attr({ inputmode: 'tel', autocomplete: 'tel' });
		phoneTop();
		restoreDetails();
		var h = window.location.hash;
		if ((h === '#bag' || h === '#checkout') && document.getElementById('bb-bag')) {
			openSheet('bb-bag');
		}
	});
})(jQuery);
