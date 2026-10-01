/*global VuFind */
/**
 * BDTD: contadores da faixa superior (instituições, dissertações, teses e documentos), com
 * as facetas instname_str e format da API de busca do próprio VuFind.
 *
 * Baseado no indicators-home.js do legado; usa fetch em vez do axios e do base.js. Como no
 * legado, "Documentos" é a soma de dissertações e teses.
 */
(function indicatorsHome() {
  function formatNumber(value) {
    return new Intl.NumberFormat("pt-BR").format(value);
  }

  function fill(id, value) {
    const element = document.getElementById(id);
    if (element) {
      element.textContent = formatNumber(value);
    }
  }

  function countOf(formats, value) {
    const format = formats.find((item) => item.value === value);
    return format ? format.count : 0;
  }

  document.addEventListener("DOMContentLoaded", async () => {
    // VuFind.path só é definido pelo layout depois deste script
    const url = VuFind.path + "/api/v1/search?type=AllFields&facet[]=format&facet[]=instname_str&sort=relevance&page=1&limit=0";
    try {
      const response = await fetch(url, { headers: { Accept: "application/json" } });
      if (!response.ok) {
        throw new Error("HTTP " + response.status);
      }
      const data = await response.json();
      const masterThesis = countOf(data.facets.format, "masterThesis");
      const doctoralThesis = countOf(data.facets.format, "doctoralThesis");
      fill("institution", data.facets.instname_str.length);
      fill("masterThesis", masterThesis);
      fill("doctorThesis", doctoralThesis);
      fill("total", masterThesis + doctoralThesis);
    } catch (error) {
      console.error(error);
    }
  });
})();
