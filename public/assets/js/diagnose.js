/* Leaf diagnoser — the signature element.
 *
 * The markup is already complete and functional without this file: every body
 * part is a <button> that scrolls to its symptom group, and every symptom is a
 * real checkbox inside a real form. This script only adds the two-way
 * highlight between the plant illustration and the symptom list.
 */
(function () {
  'use strict';

  var figure = document.querySelector('[data-diagnoser]');
  if (!figure) return;

  var hotspots = figure.querySelectorAll('.hotspot');
  var tabs = document.querySelectorAll('.part-tab');
  var groups = document.querySelectorAll('[data-part-group]');
  var counter = document.getElementById('symptom-count');
  var form = document.getElementById('diagnose-form');

  var toBn = function (n) {
    return String(n).replace(/\d/g, function (d) { return '০১২৩৪৫৬৭৮৯'[d]; });
  };

  /* Show one body part's symptoms at a time. 'all' shows everything, which is
   * also the no-JS state — nothing is hidden until this runs. */
  function showPart(part) {
    groups.forEach(function (group) {
      group.hidden = part !== 'all' && group.dataset.partGroup !== part;
    });

    hotspots.forEach(function (spot) {
      spot.setAttribute('aria-pressed', String(spot.dataset.part === part));
    });

    tabs.forEach(function (tab) {
      tab.setAttribute('aria-pressed', String(tab.dataset.part === part));
    });
  }

  hotspots.forEach(function (spot) {
    spot.addEventListener('click', function () {
      var already = spot.getAttribute('aria-pressed') === 'true';
      showPart(already ? 'all' : spot.dataset.part);

      if (!already) {
        var group = document.querySelector('[data-part-group="' + spot.dataset.part + '"]');
        if (group) group.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    });
  });

  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      showPart(tab.getAttribute('aria-pressed') === 'true' ? 'all' : tab.dataset.part);
    });
  });

  /* Live count of chosen symptoms, and a submit button that says how many. */
  function refreshCount() {
    if (!form) return;
    var n = form.querySelectorAll('input[name="symptoms[]"]:checked').length;

    if (counter) {
      counter.textContent = n === 0
        ? 'কোনো লক্ষণ বাছাই করা হয়নি'
        : toBn(n) + 'টি লক্ষণ বাছাই করা হয়েছে';
    }

    // Mark the parts that currently have a selection, so the illustration
    // reflects the form even when a different part is on screen.
    var chosenParts = {};
    form.querySelectorAll('input[name="symptoms[]"]:checked').forEach(function (input) {
      chosenParts[input.dataset.part] = true;
    });

    hotspots.forEach(function (spot) {
      spot.classList.toggle('has-selection', Boolean(chosenParts[spot.dataset.part]));
    });
  }

  if (form) {
    form.addEventListener('change', function (e) {
      if (e.target.name === 'symptoms[]') refreshCount();
    });
    refreshCount();
  }

  showPart('all');
})();
