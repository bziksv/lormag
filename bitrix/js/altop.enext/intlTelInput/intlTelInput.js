/*
 * International Telephone Input v16.0.0
 * https://github.com/jackocnr/intl-tel-input.git
 * Licensed under the MIT license
 */

// wrap in UMD
(function(factory) {
    var intlTelInput = factory(window, document);
    if (typeof module === "object" && module.exports) module.exports = intlTelInput; else window.intlTelInput = intlTelInput;
})(function(window, document, undefined) {
    "use strict";
    return function() {
        // Array of country objects for the flag dropdown.
        // Here is the criteria for the plugin to support a given country/territory
        // - It has an iso2 code: https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
        // - It has it's own country calling code (it is not a sub-region of another country): https://en.wikipedia.org/wiki/List_of_country_calling_codes
        // - It has a flag in the region-flags project: https://github.com/behdad/region-flags/tree/gh-pages/png
        // - It is supported by libphonenumber (it must be listed on this page): https://github.com/googlei18n/libphonenumber/blob/master/resources/ShortNumberMetadata.xml
        // Each country array has the following information:
        // [
        //    Country name,
        //    iso2 code,
        //    International dial code,
        //    Order (if >1 country with same dial code),
        //    Area codes
        // ]
        var allCountries = [ [ BX.message("ENEXT_ITI_COUNTRY_AF"), "af", "93" ], [ BX.message("ENEXT_ITI_COUNTRY_AL"), "al", "355" ], [ BX.message("ENEXT_ITI_COUNTRY_DZ"), "dz", "213" ], [ BX.message("ENEXT_ITI_COUNTRY_AS"), "as", "1", 5, [ "684" ] ], [ BX.message("ENEXT_ITI_COUNTRY_AD"), "ad", "376" ], [ BX.message("ENEXT_ITI_COUNTRY_AO"), "ao", "244" ], [ BX.message("ENEXT_ITI_COUNTRY_AI"), "ai", "1", 6, [ "264" ] ], [ BX.message("ENEXT_ITI_COUNTRY_AG"), "ag", "1", 7, [ "268" ] ], [ BX.message("ENEXT_ITI_COUNTRY_AR"), "ar", "54" ], [ BX.message("ENEXT_ITI_COUNTRY_AM"), "am", "374" ], [ BX.message("ENEXT_ITI_COUNTRY_AW"), "aw", "297" ], [ BX.message("ENEXT_ITI_COUNTRY_AU"), "au", "61", 0 ], [ BX.message("ENEXT_ITI_COUNTRY_AT"), "at", "43" ], [ BX.message("ENEXT_ITI_COUNTRY_AZ"), "az", "994" ], [ BX.message("ENEXT_ITI_COUNTRY_BS"), "bs", "1", 8, [ "242" ] ], [ BX.message("ENEXT_ITI_COUNTRY_BH"), "bh", "973" ], [ BX.message("ENEXT_ITI_COUNTRY_BD"), "bd", "880" ], [ BX.message("ENEXT_ITI_COUNTRY_BB"), "bb", "1", 9, [ "246" ] ], [ BX.message("ENEXT_ITI_COUNTRY_BY"), "by", "375" ], [ BX.message("ENEXT_ITI_COUNTRY_BE"), "be", "32" ], [ BX.message("ENEXT_ITI_COUNTRY_BZ"), "bz", "501" ], [ BX.message("ENEXT_ITI_COUNTRY_BJ"), "bj", "229" ], [ BX.message("ENEXT_ITI_COUNTRY_BM"), "bm", "1", 10, [ "441" ] ], [ BX.message("ENEXT_ITI_COUNTRY_BT"), "bt", "975" ], [ BX.message("ENEXT_ITI_COUNTRY_BO"), "bo", "591" ], [ BX.message("ENEXT_ITI_COUNTRY_BA"), "ba", "387" ], [ BX.message("ENEXT_ITI_COUNTRY_BW"), "bw", "267" ], [ BX.message("ENEXT_ITI_COUNTRY_BR"), "br", "55" ], [ BX.message("ENEXT_ITI_COUNTRY_IO"), "io", "246" ], [ BX.message("ENEXT_ITI_COUNTRY_VG"), "vg", "1", 11, [ "284" ] ], [ BX.message("ENEXT_ITI_COUNTRY_BN"), "bn", "673" ], [ BX.message("ENEXT_ITI_COUNTRY_BG"), "bg", "359" ], [ BX.message("ENEXT_ITI_COUNTRY_BF"), "bf", "226" ], [ BX.message("ENEXT_ITI_COUNTRY_BI"), "bi", "257" ], [ BX.message("ENEXT_ITI_COUNTRY_KH"), "kh", "855" ], [ BX.message("ENEXT_ITI_COUNTRY_CM"), "cm", "237" ], [ BX.message("ENEXT_ITI_COUNTRY_CA"), "ca", "1", 1, [ "204", "226", "236", "249", "250", "289", "306", "343", "365", "387", "403", "416", "418", "431", "437", "438", "450", "506", "514", "519", "548", "579", "581", "587", "604", "613", "639", "647", "672", "705", "709", "742", "778", "780", "782", "807", "819", "825", "867", "873", "902", "905" ] ], [ BX.message("ENEXT_ITI_COUNTRY_CV"), "cv", "238" ], [ BX.message("ENEXT_ITI_COUNTRY_BQ"), "bq", "599", 1, [ "3", "4", "7" ] ], [ BX.message("ENEXT_ITI_COUNTRY_KY"), "ky", "1", 12, [ "345" ] ], [ BX.message("ENEXT_ITI_COUNTRY_CF"), "cf", "236" ], [ BX.message("ENEXT_ITI_COUNTRY_TD"), "td", "235" ], [ BX.message("ENEXT_ITI_COUNTRY_CL"), "cl", "56" ], [ BX.message("ENEXT_ITI_COUNTRY_CN"), "cn", "86" ], [ BX.message("ENEXT_ITI_COUNTRY_CX"), "cx", "61", 2 ], [ BX.message("ENEXT_ITI_COUNTRY_CC"), "cc", "61", 1 ], [ BX.message("ENEXT_ITI_COUNTRY_CO"), "co", "57" ], [ BX.message("ENEXT_ITI_COUNTRY_KM"), "km", "269" ], [ BX.message("ENEXT_ITI_COUNTRY_CD"), "cd", "243" ], [ BX.message("ENEXT_ITI_COUNTRY_CG"), "cg", "242" ], [ BX.message("ENEXT_ITI_COUNTRY_CK"), "ck", "682" ], [ BX.message("ENEXT_ITI_COUNTRY_CR"), "cr", "506" ], [ BX.message("ENEXT_ITI_COUNTRY_CI"), "ci", "225" ], [ BX.message("ENEXT_ITI_COUNTRY_HR"), "hr", "385" ], [ BX.message("ENEXT_ITI_COUNTRY_CU"), "cu", "53" ], [ BX.message("ENEXT_ITI_COUNTRY_CW"), "cw", "599", 0 ], [ BX.message("ENEXT_ITI_COUNTRY_CY"), "cy", "357" ], [ BX.message("ENEXT_ITI_COUNTRY_CZ"), "cz", "420" ], [ BX.message("ENEXT_ITI_COUNTRY_DK"), "dk", "45" ], [ BX.message("ENEXT_ITI_COUNTRY_DJ"), "dj", "253" ], [ BX.message("ENEXT_ITI_COUNTRY_DM"), "dm", "1", 13, [ "767" ] ], [ BX.message("ENEXT_ITI_COUNTRY_DO"), "do", "1", 2, [ "809", "829", "849" ] ], [ BX.message("ENEXT_ITI_COUNTRY_EC"), "ec", "593" ], [ BX.message("ENEXT_ITI_COUNTRY_EG"), "eg", "20" ], [ BX.message("ENEXT_ITI_COUNTRY_SV"), "sv", "503" ], [ BX.message("ENEXT_ITI_COUNTRY_GQ"), "gq", "240" ], [ BX.message("ENEXT_ITI_COUNTRY_ER"), "er", "291" ], [ BX.message("ENEXT_ITI_COUNTRY_EE"), "ee", "372" ], [ BX.message("ENEXT_ITI_COUNTRY_ET"), "et", "251" ], [ BX.message("ENEXT_ITI_COUNTRY_FK"), "fk", "500" ], [ BX.message("ENEXT_ITI_COUNTRY_FO"), "fo", "298" ], [ BX.message("ENEXT_ITI_COUNTRY_FJ"), "fj", "679" ], [ BX.message("ENEXT_ITI_COUNTRY_FI"), "fi", "358", 0 ], [ BX.message("ENEXT_ITI_COUNTRY_FR"), "fr", "33" ], [ BX.message("ENEXT_ITI_COUNTRY_GF"), "gf", "594" ], [ BX.message("ENEXT_ITI_COUNTRY_PF"), "pf", "689" ], [ BX.message("ENEXT_ITI_COUNTRY_GA"), "ga", "241" ], [ BX.message("ENEXT_ITI_COUNTRY_GM"), "gm", "220" ], [ BX.message("ENEXT_ITI_COUNTRY_GE"), "ge", "995" ], [ BX.message("ENEXT_ITI_COUNTRY_DE"), "de", "49" ], [ BX.message("ENEXT_ITI_COUNTRY_GH"), "gh", "233" ], [ BX.message("ENEXT_ITI_COUNTRY_GI"), "gi", "350" ], [ BX.message("ENEXT_ITI_COUNTRY_GR"), "gr", "30" ], [ BX.message("ENEXT_ITI_COUNTRY_GL"), "gl", "299" ], [ BX.message("ENEXT_ITI_COUNTRY_GD"), "gd", "1", 14, [ "473" ] ], [ BX.message("ENEXT_ITI_COUNTRY_GP"), "gp", "590", 0 ], [ BX.message("ENEXT_ITI_COUNTRY_GU"), "gu", "1", 15, [ "671" ] ], [ BX.message("ENEXT_ITI_COUNTRY_GT"), "gt", "502" ], [ BX.message("ENEXT_ITI_COUNTRY_GG"), "gg", "44", 1, [ "1481", "7781", "7839", "7911" ] ], [ BX.message("ENEXT_ITI_COUNTRY_GN"), "gn", "224" ], [ BX.message("ENEXT_ITI_COUNTRY_GW"), "gw", "245" ], [ BX.message("ENEXT_ITI_COUNTRY_GY"), "gy", "592" ], [ BX.message("ENEXT_ITI_COUNTRY_HT"), "ht", "509" ], [ BX.message("ENEXT_ITI_COUNTRY_HN"), "hn", "504" ], [ BX.message("ENEXT_ITI_COUNTRY_HK"), "hk", "852" ], [ BX.message("ENEXT_ITI_COUNTRY_HU"), "hu", "36" ], [ BX.message("ENEXT_ITI_COUNTRY_IS"), "is", "354" ], [ BX.message("ENEXT_ITI_COUNTRY_IN"), "in", "91" ], [ BX.message("ENEXT_ITI_COUNTRY_ID"), "id", "62" ], [ BX.message("ENEXT_ITI_COUNTRY_IR"), "ir", "98" ], [ BX.message("ENEXT_ITI_COUNTRY_IQ"), "iq", "964" ], [ BX.message("ENEXT_ITI_COUNTRY_IE"), "ie", "353" ], [ BX.message("ENEXT_ITI_COUNTRY_IM"), "im", "44", 2, [ "1624", "74576", "7524", "7924", "7624" ] ], [ BX.message("ENEXT_ITI_COUNTRY_IL"), "il", "972" ], [ BX.message("ENEXT_ITI_COUNTRY_IT"), "it", "39", 0 ], [ BX.message("ENEXT_ITI_COUNTRY_JM"), "jm", "1", 4, [ "876", "658" ] ], [ BX.message("ENEXT_ITI_COUNTRY_JP"), "jp", "81" ], [ BX.message("ENEXT_ITI_COUNTRY_JE"), "je", "44", 3, [ "1534", "7509", "7700", "7797", "7829", "7937" ] ], [ BX.message("ENEXT_ITI_COUNTRY_JO"), "jo", "962" ], [ BX.message("ENEXT_ITI_COUNTRY_KZ"), "kz", "7", 1, [ "33", "7" ] ], [ BX.message("ENEXT_ITI_COUNTRY_KE"), "ke", "254" ], [ BX.message("ENEXT_ITI_COUNTRY_KI"), "ki", "686" ], [ BX.message("ENEXT_ITI_COUNTRY_XK"), "xk", "383" ], [ BX.message("ENEXT_ITI_COUNTRY_KW"), "kw", "965" ], [ BX.message("ENEXT_ITI_COUNTRY_KG"), "kg", "996" ], [ BX.message("ENEXT_ITI_COUNTRY_LA"), "la", "856" ], [ BX.message("ENEXT_ITI_COUNTRY_LV"), "lv", "371" ], [ BX.message("ENEXT_ITI_COUNTRY_LB"), "lb", "961" ], [ BX.message("ENEXT_ITI_COUNTRY_LS"), "ls", "266" ], [ BX.message("ENEXT_ITI_COUNTRY_LR"), "lr", "231" ], [ BX.message("ENEXT_ITI_COUNTRY_LY"), "ly", "218" ], [ BX.message("ENEXT_ITI_COUNTRY_LI"), "li", "423" ], [ BX.message("ENEXT_ITI_COUNTRY_LT"), "lt", "370" ], [ BX.message("ENEXT_ITI_COUNTRY_LU"), "lu", "352" ], [ BX.message("ENEXT_ITI_COUNTRY_MO"), "mo", "853" ], [ BX.message("ENEXT_ITI_COUNTRY_MK"), "mk", "389" ], [ BX.message("ENEXT_ITI_COUNTRY_MG"), "mg", "261" ], [ BX.message("ENEXT_ITI_COUNTRY_MW"), "mw", "265" ], [ BX.message("ENEXT_ITI_COUNTRY_MY"), "my", "60" ], [ BX.message("ENEXT_ITI_COUNTRY_MV"), "mv", "960" ], [ BX.message("ENEXT_ITI_COUNTRY_ML"), "ml", "223" ], [ BX.message("ENEXT_ITI_COUNTRY_MT"), "mt", "356" ], [ BX.message("ENEXT_ITI_COUNTRY_MH"), "mh", "692" ], [ BX.message("ENEXT_ITI_COUNTRY_MQ"), "mq", "596" ], [ BX.message("ENEXT_ITI_COUNTRY_MR"), "mr", "222" ], [ BX.message("ENEXT_ITI_COUNTRY_MU"), "mu", "230" ], [ BX.message("ENEXT_ITI_COUNTRY_YT"), "yt", "262", 1, [ "269", "639" ] ], [ BX.message("ENEXT_ITI_COUNTRY_MX"), "mx", "52" ], [ BX.message("ENEXT_ITI_COUNTRY_FM"), "fm", "691" ], [ BX.message("ENEXT_ITI_COUNTRY_MD"), "md", "373" ], [ BX.message("ENEXT_ITI_COUNTRY_MC"), "mc", "377" ], [ BX.message("ENEXT_ITI_COUNTRY_MN"), "mn", "976" ], [ BX.message("ENEXT_ITI_COUNTRY_ME"), "me", "382" ], [ BX.message("ENEXT_ITI_COUNTRY_MS"), "ms", "1", 16, [ "664" ] ], [ BX.message("ENEXT_ITI_COUNTRY_MA"), "ma", "212", 0 ], [ BX.message("ENEXT_ITI_COUNTRY_MZ"), "mz", "258" ], [ BX.message("ENEXT_ITI_COUNTRY_MM"), "mm", "95" ], [ BX.message("ENEXT_ITI_COUNTRY_NA"), "na", "264" ], [ BX.message("ENEXT_ITI_COUNTRY_NR"), "nr", "674" ], [ BX.message("ENEXT_ITI_COUNTRY_NP"), "np", "977" ], [ BX.message("ENEXT_ITI_COUNTRY_NL"), "nl", "31" ], [ BX.message("ENEXT_ITI_COUNTRY_NC"), "nc", "687" ], [ BX.message("ENEXT_ITI_COUNTRY_NZ"), "nz", "64" ], [ BX.message("ENEXT_ITI_COUNTRY_NI"), "ni", "505" ], [ BX.message("ENEXT_ITI_COUNTRY_NE"), "ne", "227" ], [ BX.message("ENEXT_ITI_COUNTRY_NG"), "ng", "234" ], [ BX.message("ENEXT_ITI_COUNTRY_NU"), "nu", "683" ], [ BX.message("ENEXT_ITI_COUNTRY_NF"), "nf", "672" ], [ BX.message("ENEXT_ITI_COUNTRY_KP"), "kp", "850" ], [ BX.message("ENEXT_ITI_COUNTRY_MP"), "mp", "1", 17, [ "670" ] ], [ BX.message("ENEXT_ITI_COUNTRY_NO"), "no", "47", 0 ], [ BX.message("ENEXT_ITI_COUNTRY_OM"), "om", "968" ], [ BX.message("ENEXT_ITI_COUNTRY_PK"), "pk", "92" ], [ BX.message("ENEXT_ITI_COUNTRY_PW"), "pw", "680" ], [ BX.message("ENEXT_ITI_COUNTRY_PS"), "ps", "970" ], [ BX.message("ENEXT_ITI_COUNTRY_PA"), "pa", "507" ], [ BX.message("ENEXT_ITI_COUNTRY_PG"), "pg", "675" ], [ BX.message("ENEXT_ITI_COUNTRY_PY"), "py", "595" ], [ BX.message("ENEXT_ITI_COUNTRY_PE"), "pe", "51" ], [ BX.message("ENEXT_ITI_COUNTRY_PH"), "ph", "63" ], [ BX.message("ENEXT_ITI_COUNTRY_PL"), "pl", "48" ], [ BX.message("ENEXT_ITI_COUNTRY_PT"), "pt", "351" ], [ BX.message("ENEXT_ITI_COUNTRY_PR"), "pr", "1", 3, [ "787", "939" ] ], [ BX.message("ENEXT_ITI_COUNTRY_QA"), "qa", "974" ], [ BX.message("ENEXT_ITI_COUNTRY_RE"), "re", "262", 0 ], [ BX.message("ENEXT_ITI_COUNTRY_RO"), "ro", "40" ], [ BX.message("ENEXT_ITI_COUNTRY_RU"), "ru", "7", 0 ], [ BX.message("ENEXT_ITI_COUNTRY_RW"), "rw", "250" ], [ BX.message("ENEXT_ITI_COUNTRY_BL"), "bl", "590", 1 ], [ BX.message("ENEXT_ITI_COUNTRY_SH"), "sh", "290" ], [ BX.message("ENEXT_ITI_COUNTRY_KN"), "kn", "1", 18, [ "869" ] ], [ BX.message("ENEXT_ITI_COUNTRY_LC"), "lc", "1", 19, [ "758" ] ], [ BX.message("ENEXT_ITI_COUNTRY_MF"), "mf", "590", 2 ], [ BX.message("ENEXT_ITI_COUNTRY_PM"), "pm", "508" ], [ BX.message("ENEXT_ITI_COUNTRY_VC"), "vc", "1", 20, [ "784" ] ], [ BX.message("ENEXT_ITI_COUNTRY_WS"), "ws", "685" ], [ BX.message("ENEXT_ITI_COUNTRY_SM"), "sm", "378" ], [ BX.message("ENEXT_ITI_COUNTRY_ST"), "st", "239" ], [ BX.message("ENEXT_ITI_COUNTRY_SA"), "sa", "966" ], [ BX.message("ENEXT_ITI_COUNTRY_SN"), "sn", "221" ], [ BX.message("ENEXT_ITI_COUNTRY_RS"), "rs", "381" ], [ BX.message("ENEXT_ITI_COUNTRY_SC"), "sc", "248" ], [ BX.message("ENEXT_ITI_COUNTRY_SL"), "sl", "232" ], [ BX.message("ENEXT_ITI_COUNTRY_SG"), "sg", "65" ], [ BX.message("ENEXT_ITI_COUNTRY_SX"), "sx", "1", 21, [ "721" ] ], [ BX.message("ENEXT_ITI_COUNTRY_SK"), "sk", "421" ], [ BX.message("ENEXT_ITI_COUNTRY_SI"), "si", "386" ], [ BX.message("ENEXT_ITI_COUNTRY_SB"), "sb", "677" ], [ BX.message("ENEXT_ITI_COUNTRY_SO"), "so", "252" ], [ BX.message("ENEXT_ITI_COUNTRY_ZA"), "za", "27" ], [ BX.message("ENEXT_ITI_COUNTRY_KR"), "kr", "82" ], [ BX.message("ENEXT_ITI_COUNTRY_SS"), "ss", "211" ], [ BX.message("ENEXT_ITI_COUNTRY_ES"), "es", "34" ], [ BX.message("ENEXT_ITI_COUNTRY_LK"), "lk", "94" ], [ BX.message("ENEXT_ITI_COUNTRY_SD"), "sd", "249" ], [ BX.message("ENEXT_ITI_COUNTRY_SR"), "sr", "597" ], [ BX.message("ENEXT_ITI_COUNTRY_SJ"), "sj", "47", 1, [ "79" ] ], [ BX.message("ENEXT_ITI_COUNTRY_SZ"), "sz", "268" ], [ BX.message("ENEXT_ITI_COUNTRY_SE"), "se", "46" ], [ BX.message("ENEXT_ITI_COUNTRY_CH"), "ch", "41" ], [ BX.message("ENEXT_ITI_COUNTRY_SY"), "sy", "963" ], [ BX.message("ENEXT_ITI_COUNTRY_TW"), "tw", "886" ], [ BX.message("ENEXT_ITI_COUNTRY_TJ"), "tj", "992" ], [ BX.message("ENEXT_ITI_COUNTRY_TZ"), "tz", "255" ], [ BX.message("ENEXT_ITI_COUNTRY_TH"), "th", "66" ], [ BX.message("ENEXT_ITI_COUNTRY_TL"), "tl", "670" ], [ BX.message("ENEXT_ITI_COUNTRY_TG"), "tg", "228" ], [ BX.message("ENEXT_ITI_COUNTRY_TK"), "tk", "690" ], [ BX.message("ENEXT_ITI_COUNTRY_TO"), "to", "676" ], [ BX.message("ENEXT_ITI_COUNTRY_TT"), "tt", "1", 22, [ "868" ] ], [ BX.message("ENEXT_ITI_COUNTRY_TN"), "tn", "216" ], [ BX.message("ENEXT_ITI_COUNTRY_TR"), "tr", "90" ], [ BX.message("ENEXT_ITI_COUNTRY_TM"), "tm", "993" ], [ BX.message("ENEXT_ITI_COUNTRY_TC"), "tc", "1", 23, [ "649" ] ], [ BX.message("ENEXT_ITI_COUNTRY_TV"), "tv", "688" ], [ BX.message("ENEXT_ITI_COUNTRY_VI"), "vi", "1", 24, [ "340" ] ], [ BX.message("ENEXT_ITI_COUNTRY_UG"), "ug", "256" ], [ BX.message("ENEXT_ITI_COUNTRY_UA"), "ua", "380" ], [ BX.message("ENEXT_ITI_COUNTRY_AE"), "ae", "971" ], [ BX.message("ENEXT_ITI_COUNTRY_GB"), "gb", "44", 0 ], [ BX.message("ENEXT_ITI_COUNTRY_US"), "us", "1", 0 ], [ BX.message("ENEXT_ITI_COUNTRY_UY"), "uy", "598" ], [ BX.message("ENEXT_ITI_COUNTRY_UZ"), "uz", "998" ], [ BX.message("ENEXT_ITI_COUNTRY_VU"), "vu", "678" ], [ BX.message("ENEXT_ITI_COUNTRY_VA"), "va", "39", 1, [ "06698" ] ], [ BX.message("ENEXT_ITI_COUNTRY_VE"), "ve", "58" ], [ BX.message("ENEXT_ITI_COUNTRY_VN"), "vn", "84" ], [ BX.message("ENEXT_ITI_COUNTRY_WF"), "wf", "681" ], [ BX.message("ENEXT_ITI_COUNTRY_EH"), "eh", "212", 1, [ "5288", "5289" ] ], [ BX.message("ENEXT_ITI_COUNTRY_YE"), "ye", "967" ], [ BX.message("ENEXT_ITI_COUNTRY_ZM"), "zm", "260" ], [ BX.message("ENEXT_ITI_COUNTRY_ZW"), "zw", "263" ], [ BX.message("ENEXT_ITI_COUNTRY_AX"), "ax", "358", 1, [ "18" ] ] ];
        // loop over all of the countries above, restructuring the data to be objects with named keys
		for (var i = 0; i < allCountries.length; i++) {
            var c = allCountries[i];
            allCountries[i] = {
                name: c[0],
                iso2: c[1],
                dialCode: c[2],
                priority: c[3] || 0,
                areaCodes: c[4] || null
            };
        }
		allCountries.sort(function(a,b) {
			var x = a.name.toLowerCase(),
				y = b.name.toLowerCase();
			
			return x < y ? -1 : x > y ? 1 : 0;
		});
		"use strict";
        function _classCallCheck(instance, Constructor) {
            if (!(instance instanceof Constructor)) {
                throw new TypeError("Cannot call a class as a function");
            }
        }
        function _defineProperties(target, props) {
            for (var i = 0; i < props.length; i++) {
                var descriptor = props[i];
                descriptor.enumerable = descriptor.enumerable || false;
                descriptor.configurable = true;
                if ("value" in descriptor) descriptor.writable = true;
                Object.defineProperty(target, descriptor.key, descriptor);
            }
        }
        function _createClass(Constructor, protoProps, staticProps) {
            if (protoProps) _defineProperties(Constructor.prototype, protoProps);
            if (staticProps) _defineProperties(Constructor, staticProps);
            return Constructor;
        }
        window.intlTelInputGlobals = {
            getInstance: function getInstance(input) {
                var id = input.getAttribute("data-intl-tel-input-id");
                return window.intlTelInputGlobals.instances[id];
            },
            instances: {}
        };
        // these vars persist through all instances of the plugin
        var id = 0;
        var defaults = {
            // whether or not to allow the dropdown
            allowDropdown: true,
            // if there is just a dial code in the input: remove it on blur
            autoHideDialCode: true,
            // add a placeholder in the input with an example number for the selected country
            autoPlaceholder: "polite",
            // modify the parentClass
            customContainer: "",
            // modify the auto placeholder
            customPlaceholder: null,
            // append menu to specified element
            dropdownContainer: null,
            // don't display these countries
            excludeCountries: [],
            // format the input value during initialisation and on setNumber
            formatOnDisplay: true,
            // geoIp lookup function
            geoIpLookup: null,
            // inject a hidden input with this name, and on submit, populate it with the result of getNumber
            hiddenInput: "",
            // initial country
            initialCountry: "",
            // localized country names e.g. { 'de': 'Deutschland' }
            localizedCountries: null,
            // don't insert international dial codes
            nationalMode: true,
            // display only these countries
            onlyCountries: [],
            // number type to use for placeholders
            placeholderNumberType: "MOBILE",
            // the countries at the top of the list. defaults to united states and united kingdom
            preferredCountries: [ "us", "gb" ],
            // display the country dial code next to the selected flag so it's not part of the typed number
            separateDialCode: false,
            // specify the path to the libphonenumber script to enable validation/formatting
            utilsScript: ""
        };
        // https://en.wikipedia.org/wiki/List_of_North_American_Numbering_Plan_area_codes#Non-geographic_area_codes
        var regionlessNanpNumbers = [ "800", "822", "833", "844", "855", "866", "877", "880", "881", "882", "883", "884", "885", "886", "887", "888", "889" ];
        // keep track of if the window.load event has fired as impossible to check after the fact
        window.addEventListener("load", function() {
            // UPDATE: use a public static field so we can fudge it in the tests
            window.intlTelInputGlobals.windowLoaded = true;
        });
        // utility function to iterate over an object. can't use Object.entries or native forEach because
        // of IE11
        var forEachProp = function forEachProp(obj, callback) {
            var keys = Object.keys(obj);
            for (var i = 0; i < keys.length; i++) {
                callback(keys[i], obj[keys[i]]);
            }
        };
        // run a method on each instance of the plugin
        var forEachInstance = function forEachInstance(method) {
            forEachProp(window.intlTelInputGlobals.instances, function(key) {
                window.intlTelInputGlobals.instances[key][method]();
            });
        };
        // this is our plugin class that we will create an instance of
        // eslint-disable-next-line no-unused-vars
        var Iti = /*#__PURE__*/
        function() {
            function Iti(input, options) {
                var _this = this;
                _classCallCheck(this, Iti);
                this.id = id++;
                this.telInput = input;
                this.activeItem = null;
                this.highlightedItem = null;
                // process specified options / defaults
                // alternative to Object.assign, which isn't supported by IE11
                var customOptions = options || {};
                this.options = {};
                forEachProp(defaults, function(key, value) {
                    _this.options[key] = customOptions.hasOwnProperty(key) ? customOptions[key] : value;
                });
                this.hadInitialPlaceholder = Boolean(input.getAttribute("placeholder"));
            }
            _createClass(Iti, [ {
                key: "_init",
                value: function _init() {
                    var _this2 = this;
                    // if in nationalMode, disable options relating to dial codes
                    if (this.options.nationalMode) this.options.autoHideDialCode = false;
                    // if separateDialCode then doesn't make sense to A) insert dial code into input
                    // (autoHideDialCode), and B) display national numbers (because we're displaying the country
                    // dial code next to them)
                    if (this.options.separateDialCode) {
                        this.options.autoHideDialCode = this.options.nationalMode = false;
                    }
                    // we cannot just test screen size as some smartphones/website meta tags will report desktop
                    // resolutions
                    // Note: for some reason jasmine breaks if you put this in the main Plugin function with the
                    // rest of these declarations
                    // Note: to target Android Mobiles (and not Tablets), we must find 'Android' and 'Mobile'
                    this.isMobile = /Android.+Mobile|webOS|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
					if (this.isMobile) {
                        // trigger the mobile dropdown css
                        document.body.classList.add("iti-mobile");
                        // on mobile, we want a full screen dropdown, so we must append it to the body
                        if (!this.options.dropdownContainer) this.options.dropdownContainer = document.body;
                    }
                    // these promises get resolved when their individual requests complete
                    // this way the dev can do something like iti.promise.then(...) to know when all requests are
                    // complete
                    if (typeof Promise !== "undefined") {
                        var autoCountryPromise = new Promise(function(resolve, reject) {
                            _this2.resolveAutoCountryPromise = resolve;
                            _this2.rejectAutoCountryPromise = reject;
                        });
                        var utilsScriptPromise = new Promise(function(resolve, reject) {
                            _this2.resolveUtilsScriptPromise = resolve;
                            _this2.rejectUtilsScriptPromise = reject;
                        });
                        this.promise = Promise.all([ autoCountryPromise, utilsScriptPromise ]);
                    } else {
                        // prevent errors when Promise doesn't exist
                        this.resolveAutoCountryPromise = this.rejectAutoCountryPromise = function() {};
                        this.resolveUtilsScriptPromise = this.rejectUtilsScriptPromise = function() {};
                    }
                    // in various situations there could be no country selected initially, but we need to be able
                    // to assume this variable exists
                    this.selectedCountryData = {};
                    // process all the data: onlyCountries, excludeCountries, preferredCountries etc
                    this._processCountryData();
                    // generate the markup
                    this._generateMarkup();
                    // set the initial state of the input value and the selected flag
                    this._setInitialState();
                    // start all of the event listeners: autoHideDialCode, input keydown, selectedFlag click
                    this._initListeners();
                    // utils script, and auto country
                    this._initRequests();
                }
            }, {
                key: "_processCountryData",
                value: function _processCountryData() {
                    // process onlyCountries or excludeCountries array if present
                    this._processAllCountries();
                    // process the countryCodes map
                    this._processCountryCodes();
                    // process the preferredCountries
                    this._processPreferredCountries();
                    // translate countries according to localizedCountries option
                    if (this.options.localizedCountries) this._translateCountriesByLocale();
                    // sort countries by name
                    if (this.options.onlyCountries.length || this.options.localizedCountries) {
                        this.countries.sort(this._countryNameSort);
                    }
                }
            }, {
                key: "_addCountryCode",
                value: function _addCountryCode(iso2, dialCode, priority) {
                    if (dialCode.length > this.dialCodeMaxLen) {
                        this.dialCodeMaxLen = dialCode.length;
                    }
                    if (!this.countryCodes.hasOwnProperty(dialCode)) {
                        this.countryCodes[dialCode] = [];
                    }
                    // bail if we already have this country for this dialCode
                    for (var i = 0; i < this.countryCodes[dialCode].length; i++) {
                        if (this.countryCodes[dialCode][i] === iso2) return;
                    }
                    // check for undefined as 0 is falsy
                    var index = priority !== undefined ? priority : this.countryCodes[dialCode].length;
                    this.countryCodes[dialCode][index] = iso2;
                }
            }, {
                key: "_processAllCountries",
                value: function _processAllCountries() {
                    if (this.options.onlyCountries.length) {
                        var lowerCaseOnlyCountries = this.options.onlyCountries.map(function(country) {
                            return country.toLowerCase();
                        });
                        this.countries = allCountries.filter(function(country) {
                            return lowerCaseOnlyCountries.indexOf(country.iso2) > -1;
                        });
                    } else if (this.options.excludeCountries.length) {
                        var lowerCaseExcludeCountries = this.options.excludeCountries.map(function(country) {
                            return country.toLowerCase();
                        });
                        this.countries = allCountries.filter(function(country) {
                            return lowerCaseExcludeCountries.indexOf(country.iso2) === -1;
                        });
                    } else {
                        this.countries = allCountries;
                    }
                }
            }, {
                key: "_translateCountriesByLocale",
                value: function _translateCountriesByLocale() {
                    for (var i = 0; i < this.countries.length; i++) {
                        var iso = this.countries[i].iso2.toLowerCase();
                        if (this.options.localizedCountries.hasOwnProperty(iso)) {
                            this.countries[i].name = this.options.localizedCountries[iso];
                        }
                    }
                }
            }, {
                key: "_countryNameSort",
                value: function _countryNameSort(a, b) {
                    return a.name.localeCompare(b.name);
                }
            }, {
                key: "_processCountryCodes",
                value: function _processCountryCodes() {
                    this.dialCodeMaxLen = 0;
                    this.countryCodes = {};
                    // first: add dial codes
                    for (var i = 0; i < this.countries.length; i++) {
                        var c = this.countries[i];
                        this._addCountryCode(c.iso2, c.dialCode, c.priority);
                    }
                    // next: add area codes
                    // this is a second loop over countries, to make sure we have all of the "root" countries
                    // already in the map, so that we can access them, as each time we add an area code substring
                    // to the map, we also need to include the "root" country's code, as that also matches
                    for (var _i = 0; _i < this.countries.length; _i++) {
                        var _c = this.countries[_i];
                        // area codes
                        if (_c.areaCodes) {
                            var rootCountryCode = this.countryCodes[_c.dialCode][0];
                            // for each area code
                            for (var j = 0; j < _c.areaCodes.length; j++) {
                                var areaCode = _c.areaCodes[j];
                                // for each digit in the area code to add all partial matches as well
                                for (var k = 1; k < areaCode.length; k++) {
                                    var partialDialCode = _c.dialCode + areaCode.substr(0, k);
                                    // start with the root country, as that also matches this dial code
                                    this._addCountryCode(rootCountryCode, partialDialCode);
                                    this._addCountryCode(_c.iso2, partialDialCode);
                                }
                                // add the full area code
                                this._addCountryCode(_c.iso2, _c.dialCode + areaCode);
                            }
                        }
                    }
                }
            }, {
                key: "_processPreferredCountries",
                value: function _processPreferredCountries() {
                    this.preferredCountries = [];
                    for (var i = 0; i < this.options.preferredCountries.length; i++) {
                        var countryCode = this.options.preferredCountries[i].toLowerCase();
                        var countryData = this._getCountryData(countryCode, false, true);
                        if (countryData) this.preferredCountries.push(countryData);
                    }
                }
            }, {
                key: "_createEl",
                value: function _createEl(name, attrs, container) {
                    var el = document.createElement(name);
                    if (attrs) forEachProp(attrs, function(key, value) {
                        return el.setAttribute(key, value);
                    });
                    if (container) container.appendChild(el);
                    return el;
                }
            }, {
                key: "_generateMarkup",
                value: function _generateMarkup() {
                    // prevent autocomplete as there's no safe, cross-browser event we can react to, so it can
                    // easily put the plugin in an inconsistent state e.g. the wrong flag selected for the
                    // autocompleted number, which on submit could mean wrong number is saved (esp in nationalMode)
                    this.telInput.setAttribute("autocomplete", "off");
                    // containers (mostly for positioning)
                    var parentClass = "iti";
                    if (this.options.allowDropdown) parentClass += " iti--allow-dropdown";
                    if (this.options.separateDialCode) parentClass += " iti--separate-dial-code";
                    if (this.options.customContainer) {
                        parentClass += " ";
                        parentClass += this.options.customContainer;
                    }
                    var wrapper = this._createEl("div", {
                        "class": parentClass
                    });
                    this.telInput.parentNode.insertBefore(wrapper, this.telInput);
                    this.flagsContainer = this._createEl("div", {
                        "class": "iti__flag-container"
                    }, wrapper);
                    wrapper.appendChild(this.telInput);
                    // selected flag (displayed to left of input)
                    this.selectedFlag = this._createEl("div", {
                        "class": "iti__selected-flag",
                        role: "combobox",
                        "aria-owns": "country-listbox"
                    }, this.flagsContainer);
					if (this.options.allowDropdown) {
                        // make element focusable and tab navigable
                        this.selectedFlag.setAttribute("tabindex", "0");
                        this.dropdownArrow = this._createEl("i", {
                            "class": "icon-arrow-down"
                        }, this.selectedFlag);
                        // country dropdown: preferred countries, then divider, then all countries
                        this.countryList = this._createEl("ul", {
                            "class": "iti__country-list iti__hide",
                            id: "country-listbox",
                            "aria-expanded": "false",
                            role: "listbox"
                        });
                        if (this.preferredCountries.length) {
                            this._appendListItems(this.preferredCountries, "iti__preferred");
                            this._createEl("li", {
                                "class": "iti__divider",
                                role: "separator",
                                "aria-disabled": "true"
                            }, this.countryList);
                        }
                        this._appendListItems(this.countries, "iti__standard");
                        // create dropdownContainer markup
                        if (this.options.dropdownContainer) {
                            this.dropdown = this._createEl("div", {
                                "class": "iti iti--container"
                            });
                            this.dropdown.appendChild(this.countryList);
                        } else {
                            this.flagsContainer.appendChild(this.countryList);
                        }
                    }
                    this.selectedFlagInner = this._createEl("div", {
                        "class": "iti__flag"
                    }, this.selectedFlag);
                    if (this.options.separateDialCode) {
                        this.selectedDialCode = this._createEl("div", {
                            "class": "iti__selected-dial-code"
                        }, this.selectedFlag);
                    }                    
                    if (this.options.hiddenInput) {
                        var hiddenInputName = this.options.hiddenInput;
                        var name = this.telInput.getAttribute("name");
                        if (name) {
                            var i = name.lastIndexOf("[");
                            // if input name contains square brackets, then give the hidden input the same name,
                            // replacing the contents of the last set of brackets with the given hiddenInput name
                            if (i !== -1) hiddenInputName = "".concat(name.substr(0, i), "[").concat(hiddenInputName, "]");
                        }
                        this.hiddenInput = this._createEl("input", {
                            type: "hidden",
                            name: hiddenInputName
                        });
                        wrapper.appendChild(this.hiddenInput);
                    }
                }
            }, {
                key: "_appendListItems",
                value: function _appendListItems(countries, className) {
                    // we create so many DOM elements, it is faster to build a temp string
                    // and then add everything to the DOM in one go at the end
                    var tmp = "";
                    // for each country
                    for (var i = 0; i < countries.length; i++) {
                        var c = countries[i];
                        // open the list item
                        tmp += "<li class='iti__country ".concat(className, "' tabIndex='-1' id='iti-item-").concat(c.iso2, "' role='option' data-dial-code='").concat(c.dialCode, "' data-country-code='").concat(c.iso2, "'>");
                        // add the flag
                        tmp += "<div class='iti__flag-box'><div class='iti__flag iti__".concat(c.iso2, "'></div></div>");
                        // and the country name and dial code
                        tmp += "<span class='iti__country-name'>".concat(c.name, "</span>");
                        tmp += "<span class='iti__dial-code'>+".concat(c.dialCode, "</span>");
                        // close the list item
                        tmp += "</li>";
                    }
                    this.countryList.insertAdjacentHTML("beforeend", tmp);
                }
            }, {
                key: "_setInitialState",
                value: function _setInitialState() {
                    var val = this.telInput.value;
                    var dialCode = this._getDialCode(val);
                    var isRegionlessNanp = this._isRegionlessNanp(val);
                    var _this$options = this.options, initialCountry = _this$options.initialCountry, nationalMode = _this$options.nationalMode, autoHideDialCode = _this$options.autoHideDialCode, separateDialCode = _this$options.separateDialCode;
                    // if we already have a dial code, and it's not a regionlessNanp, we can go ahead and set the
                    // flag, else fall back to the default country
                    if (dialCode && !isRegionlessNanp) {
                        this._updateFlagFromNumber(val);
                    } else if (initialCountry !== "auto") {
                        // see if we should select a flag
                        if (initialCountry) {
                            this._setFlag(initialCountry.toLowerCase());
                        } else {
                            if (dialCode && isRegionlessNanp) {
                                // has intl dial code, is regionless nanp, and no initialCountry, so default to US
                                this._setFlag("us");
                            } else {
                                // no dial code and no initialCountry, so default to first in list
                                this.defaultCountry = this.preferredCountries.length ? this.preferredCountries[0].iso2 : this.countries[0].iso2;
                                if (!val) {
                                    this._setFlag(this.defaultCountry);
                                }
                            }
                        }
                        // if empty and no nationalMode and no autoHideDialCode then insert the default dial code
                        if (!val && !nationalMode && !autoHideDialCode && !separateDialCode) {
                            this.telInput.value = "+".concat(this.selectedCountryData.dialCode);
                        }
                    }
                    // NOTE: if initialCountry is set to auto, that will be handled separately
                    // format - note this wont be run after _updateDialCode as that's only called if no val
                    if (val) this._updateValFromNumber(val);
                }
            }, {
                key: "_initListeners",
                value: function _initListeners() {
                    this._initKeyListeners();
                    if (this.options.autoHideDialCode) this._initBlurListeners();
                    if (this.options.allowDropdown) this._initDropdownListeners();
                    if (this.hiddenInput) this._initHiddenInputListener();
                }
            }, {
                key: "_initHiddenInputListener",
                value: function _initHiddenInputListener() {
                    var _this3 = this;
                    this._handleHiddenInputSubmit = function() {
                        _this3.hiddenInput.value = _this3.getNumber();
                    };
                    if (this.telInput.form) this.telInput.form.addEventListener("submit", this._handleHiddenInputSubmit);
                }
            }, {
                key: "_getClosestLabel",
                value: function _getClosestLabel() {
                    var el = this.telInput;
                    while (el && el.tagName !== "LABEL") {
                        el = el.parentNode;
                    }
                    return el;
                }
            }, {
                key: "_initDropdownListeners",
                value: function _initDropdownListeners() {
                    var _this4 = this;
                    // hack for input nested inside label (which is valid markup): clicking the selected-flag to
                    // open the dropdown would then automatically trigger a 2nd click on the input which would
                    // close it again
                    this._handleLabelClick = function(e) {
                        // if the dropdown is closed, then focus the input, else ignore the click
                        if (_this4.countryList.classList.contains("iti__hide")) _this4.telInput.focus(); else e.preventDefault();
                    };
                    var label = this._getClosestLabel();
                    if (label) label.addEventListener("click", this._handleLabelClick);
                    // toggle country dropdown on click
                    this._handleClickSelectedFlag = function() {
                        // only intercept this event if we're opening the dropdown
                        // else let it bubble up to the top ("click-off-to-close" listener)
                        // we cannot just stopPropagation as it may be needed to close another instance
                        if (_this4.countryList.classList.contains("iti__hide") && !_this4.telInput.disabled && !_this4.telInput.readOnly) {
                            _this4._showDropdown();
                        }
                    };
                    this.selectedFlag.addEventListener("click", this._handleClickSelectedFlag);
                    // open dropdown list if currently focused
                    this._handleFlagsContainerKeydown = function(e) {
                        var isDropdownHidden = _this4.countryList.classList.contains("iti__hide");
                        if (isDropdownHidden && [ "ArrowUp", "ArrowDown", " ", "Enter" ].indexOf(e.key) !== -1) {
                            // prevent form from being submitted if "ENTER" was pressed
                            e.preventDefault();
                            // prevent event from being handled again by document
                            e.stopPropagation();
                            _this4._showDropdown();
                        }
                        // allow navigation from dropdown to input on TAB
                        if (e.key === "Tab") _this4._closeDropdown();
                    };
                    this.flagsContainer.addEventListener("keydown", this._handleFlagsContainerKeydown);
                }
            }, {
                key: "_initRequests",
                value: function _initRequests() {
                    var _this5 = this;
                    // if the user has specified the path to the utils script, fetch it on window.load, else resolve
                    if (this.options.utilsScript && !window.intlTelInputUtils) {
                        // if the plugin is being initialised after the window.load event has already been fired
                        if (window.intlTelInputGlobals.windowLoaded) {
                            window.intlTelInputGlobals.loadUtils(this.options.utilsScript);
                        } else {
                            // wait until the load event so we don't block any other requests e.g. the flags image
                            window.addEventListener("load", function() {
                                window.intlTelInputGlobals.loadUtils(_this5.options.utilsScript);
                            });
                        }
                    } else this.resolveUtilsScriptPromise();
                    if (this.options.initialCountry === "auto") this._loadAutoCountry(); else this.resolveAutoCountryPromise();
                }
            }, {
                key: "_loadAutoCountry",
                value: function _loadAutoCountry() {
                    // 3 options:
                    // 1) already loaded (we're done)
                    // 2) not already started loading (start)
                    // 3) already started loading (do nothing - just wait for loading callback to fire)
                    if (window.intlTelInputGlobals.autoCountry) {
                        this.handleAutoCountry();
                    } else if (!window.intlTelInputGlobals.startedLoadingAutoCountry) {
                        // don't do this twice!
                        window.intlTelInputGlobals.startedLoadingAutoCountry = true;
                        if (typeof this.options.geoIpLookup === "function") {
                            this.options.geoIpLookup(function(countryCode) {
                                window.intlTelInputGlobals.autoCountry = countryCode.toLowerCase();
                                // tell all instances the auto country is ready
                                // TODO: this should just be the current instances
                                // UPDATE: use setTimeout in case their geoIpLookup function calls this callback straight
                                // away (e.g. if they have already done the geo ip lookup somewhere else). Using
                                // setTimeout means that the current thread of execution will finish before executing
                                // this, which allows the plugin to finish initialising.
                                setTimeout(function() {
                                    return forEachInstance("handleAutoCountry");
                                });
                            }, function() {
                                return forEachInstance("rejectAutoCountryPromise");
                            });
                        }
                    }
                }
            }, {
                key: "_initKeyListeners",
                value: function _initKeyListeners() {
                    var _this6 = this;
                    // update flag on keyup
                    this._handleKeyupEvent = function() {
                        if (_this6._updateFlagFromNumber(_this6.telInput.value)) {
                            _this6._triggerCountryChange();
                        }
                    };
                    this.telInput.addEventListener("keyup", this._handleKeyupEvent);
                    // update flag on cut/paste events (now supported in all major browsers)
                    this._handleClipboardEvent = function() {
                        // hack because "paste" event is fired before input is updated
                        setTimeout(_this6._handleKeyupEvent);
                    };
                    this.telInput.addEventListener("cut", this._handleClipboardEvent);
                    this.telInput.addEventListener("paste", this._handleClipboardEvent);
                }
            }, {
                key: "_cap",
                value: function _cap(number) {
                    var max = this.telInput.getAttribute("maxlength");
                    return max && number.length > max ? number.substr(0, max) : number;
                }
            }, {
                key: "_initBlurListeners",
                value: function _initBlurListeners() {
                    var _this7 = this;
                    // on blur or form submit: if just a dial code then remove it
                    this._handleSubmitOrBlurEvent = function() {
                        _this7._removeEmptyDialCode();
                    };
                    if (this.telInput.form) this.telInput.form.addEventListener("submit", this._handleSubmitOrBlurEvent);
                    this.telInput.addEventListener("blur", this._handleSubmitOrBlurEvent);
                }
            }, {
                key: "_removeEmptyDialCode",
                value: function _removeEmptyDialCode() {
                    if (this.telInput.value.charAt(0) === "+") {
                        var numeric = this._getNumeric(this.telInput.value);
                        // if just a plus, or if just a dial code
                        if (!numeric || this.selectedCountryData.dialCode === numeric) {
                            this.telInput.value = "";
                        }
                    }
                }
            }, {
                key: "_getNumeric",
                value: function _getNumeric(s) {
                    return s.replace(/\D/g, "");
                }
            }, {
                key: "_trigger",
                value: function _trigger(name) {
                    // have to use old school document.createEvent as IE11 doesn't support `new Event()` syntax
                    var e = document.createEvent("Event");
                    e.initEvent(name, true, true);
                    // can bubble, and is cancellable
                    this.telInput.dispatchEvent(e);
                }
            }, {
                key: "_showDropdown",
                value: function _showDropdown() {
                    this.countryList.classList.remove("iti__hide");
                    this.countryList.setAttribute("aria-expanded", "true");
                    this._setDropdownPosition();
                    // update highlighting and scroll to active list item
                    if (this.activeItem) {
                        this._highlightListItem(this.activeItem, false);
                        this._scrollTo(this.activeItem, true);
                    }
                    // bind all the dropdown-related listeners: mouseover, click, click-off, keydown
                    this._bindDropdownListeners();
                    // update the arrow
					this.dropdownArrow.classList.remove("icon-arrow-down");
                    this.dropdownArrow.classList.add("icon-arrow-up");
                    this._trigger("open:countrydropdown");
                }
            }, {
                key: "_toggleClass",
                value: function _toggleClass(el, className, shouldHaveClass) {
                    if (shouldHaveClass && !el.classList.contains(className)) el.classList.add(className); else if (!shouldHaveClass && el.classList.contains(className)) el.classList.remove(className);
                }
            }, {
                key: "_setDropdownPosition",
                value: function _setDropdownPosition() {
                    var _this8 = this;
                    if (this.options.dropdownContainer) {
                        this.options.dropdownContainer.appendChild(this.dropdown);
                    }
                    if (!this.isMobile) {
                        var pos = this.telInput.getBoundingClientRect();
                        // windowTop from https://stackoverflow.com/a/14384091/217866
                        var windowTop = window.pageYOffset || document.documentElement.scrollTop;
                        var inputTop = pos.top + windowTop;
                        var dropdownHeight = this.countryList.offsetHeight;
                        // dropdownFitsBelow = (dropdownBottom < windowBottom)
                        var dropdownFitsBelow = inputTop + this.telInput.offsetHeight + dropdownHeight < windowTop + window.innerHeight;
                        var dropdownFitsAbove = inputTop - dropdownHeight > windowTop;
                        // by default, the dropdown will be below the input. If we want to position it above the
                        // input, we add the dropup class.
                        this._toggleClass(this.countryList, "iti__country-list--dropup", !dropdownFitsBelow && dropdownFitsAbove);
                        // if dropdownContainer is enabled, calculate postion
                        if (this.options.dropdownContainer) {
                            // by default the dropdown will be directly over the input because it's not in the flow.
                            // If we want to position it below, we need to add some extra top value.
                            var extraTop = !dropdownFitsBelow && dropdownFitsAbove ? 0 : this.telInput.offsetHeight;
                            // calculate placement
                            this.dropdown.style.top = "".concat(inputTop + extraTop, "px");
                            this.dropdown.style.left = "".concat(pos.left + document.body.scrollLeft, "px");
                            // close menu on window scroll
                            this._handleWindowScroll = function() {
                                return _this8._closeDropdown();
                            };
                            window.addEventListener("scroll", this._handleWindowScroll);
                        }
                    }
                }
            }, {
                key: "_getClosestListItem",
                value: function _getClosestListItem(target) {
                    var el = target;
                    while (el && el !== this.countryList && !el.classList.contains("iti__country")) {
                        el = el.parentNode;
                    }
                    // if we reached the countryList element, then return null
                    return el === this.countryList ? null : el;
                }
            }, {
                key: "_bindDropdownListeners",
                value: function _bindDropdownListeners() {
                    var _this9 = this;
                    // when mouse over a list item, just highlight that one
                    // we add the class "highlight", so if they hit "enter" we know which one to select
                    this._handleMouseoverCountryList = function(e) {
                        // handle event delegation, as we're listening for this event on the countryList
                        var listItem = _this9._getClosestListItem(e.target);
                        if (listItem) _this9._highlightListItem(listItem, false);
                    };
                    this.countryList.addEventListener("mouseover", this._handleMouseoverCountryList);
                    // listen for country selection
                    this._handleClickCountryList = function(e) {
                        var listItem = _this9._getClosestListItem(e.target);
                        if (listItem) _this9._selectListItem(listItem);
                    };
                    this.countryList.addEventListener("click", this._handleClickCountryList);
                    // click off to close
                    // (except when this initial opening click is bubbling up)
                    // we cannot just stopPropagation as it may be needed to close another instance
                    var isOpening = true;
                    this._handleClickOffToClose = function() {
                        if (!isOpening) _this9._closeDropdown();
                        isOpening = false;
                    };
                    document.documentElement.addEventListener("click", this._handleClickOffToClose);
                    // listen for up/down scrolling, enter to select, or letters to jump to country name.
                    // use keydown as keypress doesn't fire for non-char keys and we want to catch if they
                    // just hit down and hold it to scroll down (no keyup event).
                    // listen on the document because that's where key events are triggered if no input has focus
                    var query = "";
                    var queryTimer = null;
                    this._handleKeydownOnDropdown = function(e) {
                        // prevent down key from scrolling the whole page,
                        // and enter key from submitting a form etc
                        e.preventDefault();
                        // up and down to navigate
                        if (e.key === "ArrowUp" || e.key === "ArrowDown") _this9._handleUpDownKey(e.key); else if (e.key === "Enter") _this9._handleEnterKey(); else if (e.key === "Escape") _this9._closeDropdown(); else if (/^[a-zA-ZA-y ]$/.test(e.key)) {
                            // jump to countries that start with the query string
                            if (queryTimer) clearTimeout(queryTimer);
                            query += e.key.toLowerCase();
                            _this9._searchForCountry(query);
                            // if the timer hits 1 second, reset the query
                            queryTimer = setTimeout(function() {
                                query = "";
                            }, 1e3);
                        }
                    };
                    document.addEventListener("keydown", this._handleKeydownOnDropdown);
                }
            }, {
                key: "_handleUpDownKey",
                value: function _handleUpDownKey(key) {
                    var next = key === "ArrowUp" ? this.highlightedItem.previousElementSibling : this.highlightedItem.nextElementSibling;
                    if (next) {
                        // skip the divider
                        if (next.classList.contains("iti__divider")) {
                            next = key === "ArrowUp" ? next.previousElementSibling : next.nextElementSibling;
                        }
                        this._highlightListItem(next, true);
                    }
                }
            }, {
                key: "_handleEnterKey",
                value: function _handleEnterKey() {
                    if (this.highlightedItem) this._selectListItem(this.highlightedItem);
                }
            }, {
                key: "_searchForCountry",
                value: function _searchForCountry(query) {
                    for (var i = 0; i < this.countries.length; i++) {
                        if (this._startsWith(this.countries[i].name, query)) {
                            var listItem = this.countryList.querySelector("#iti-item-".concat(this.countries[i].iso2));
                            // update highlighting and scroll
                            this._highlightListItem(listItem, false);
                            this._scrollTo(listItem, true);
                            break;
                        }
                    }
                }
            }, {
                key: "_startsWith",
                value: function _startsWith(a, b) {
                    return a.substr(0, b.length).toLowerCase() === b;
                }
            }, {
                key: "_updateValFromNumber",
                value: function _updateValFromNumber(originalNumber) {
                    var number = originalNumber;
                    if (this.options.formatOnDisplay && window.intlTelInputUtils && this.selectedCountryData) {
                        var useNational = !this.options.separateDialCode && (this.options.nationalMode || number.charAt(0) !== "+");
                        var _intlTelInputUtils$nu = intlTelInputUtils.numberFormat, NATIONAL = _intlTelInputUtils$nu.NATIONAL, INTERNATIONAL = _intlTelInputUtils$nu.INTERNATIONAL;
                        var format = useNational ? NATIONAL : INTERNATIONAL;
                        number = intlTelInputUtils.formatNumber(number, this.selectedCountryData.iso2, format);
                    }
                    number = this._beforeSetNumber(number);
                    this.telInput.value = number;
                }
            }, {
                key: "_updateFlagFromNumber",
                value: function _updateFlagFromNumber(originalNumber) {
                    // if we're in nationalMode and we already have US/Canada selected, make sure the number starts
                    // with a +1 so _getDialCode will be able to extract the area code
                    // update: if we dont yet have selectedCountryData, but we're here (trying to update the flag
                    // from the number), that means we're initialising the plugin with a number that already has a
                    // dial code, so fine to ignore this bit
                    var number = originalNumber;
                    var selectedDialCode = this.selectedCountryData.dialCode;
                    var isNanp = selectedDialCode === "1";
                    if (number && this.options.nationalMode && isNanp && number.charAt(0) !== "+") {
                        if (number.charAt(0) !== "1") number = "1".concat(number);
                        number = "+".concat(number);
                    }
                    // update flag if user types area code for another country
                    if (this.options.separateDialCode && selectedDialCode && number.charAt(0) !== "+") {
                        number = "+".concat(selectedDialCode).concat(number);
                    }
                    // try and extract valid dial code from input
                    var dialCode = this._getDialCode(number);
                    var numeric = this._getNumeric(number);
                    var countryCode = null;
                    if (dialCode) {
                        var countryCodes = this.countryCodes[this._getNumeric(dialCode)];
                        // check if the right country is already selected. this should be false if the number is
                        // longer than the matched dial code because in this case we need to make sure that if
                        // there are multiple country matches, that the first one is selected (note: we could
                        // just check that here, but it requires the same loop that we already have later)
                        var alreadySelected = countryCodes.indexOf(this.selectedCountryData.iso2) !== -1 && numeric.length <= dialCode.length - 1;
                        var isRegionlessNanpNumber = selectedDialCode === "1" && this._isRegionlessNanp(numeric);
                        // only update the flag if:
                        // A) NOT (we currently have a NANP flag selected, and the number is a regionlessNanp)
                        // AND
                        // B) the right country is not already selected
                        if (!isRegionlessNanpNumber && !alreadySelected) {
                            // if using onlyCountries option, countryCodes[0] may be empty, so we must find the first
                            // non-empty index
                            for (var j = 0; j < countryCodes.length; j++) {
                                if (countryCodes[j]) {
                                    countryCode = countryCodes[j];
                                    break;
                                }
                            }
                        }
                    } else if (number.charAt(0) === "+" && numeric.length) {
                        // invalid dial code, so empty
                        // Note: use getNumeric here because the number has not been formatted yet, so could contain
                        // bad chars
                        countryCode = "";
                    } else if (!number || number === "+") {
                        // empty, or just a plus, so default
                        countryCode = this.defaultCountry;
                    }
                    if (countryCode !== null) {
                        return this._setFlag(countryCode);
                    }
                    return false;
                }
            }, {
                key: "_isRegionlessNanp",
                value: function _isRegionlessNanp(number) {
                    var numeric = this._getNumeric(number);
                    if (numeric.charAt(0) === "1") {
                        var areaCode = numeric.substr(1, 3);
                        return regionlessNanpNumbers.indexOf(areaCode) !== -1;
                    }
                    return false;
                }
            }, {
                key: "_highlightListItem",
                value: function _highlightListItem(listItem, shouldFocus) {
                    var prevItem = this.highlightedItem;
                    if (prevItem) prevItem.classList.remove("iti__highlight");
                    this.highlightedItem = listItem;
                    this.highlightedItem.classList.add("iti__highlight");
                    if (shouldFocus) this.highlightedItem.focus();
                }
            }, {
                key: "_getCountryData",
                value: function _getCountryData(countryCode, ignoreOnlyCountriesOption, allowFail) {
                    var countryList = ignoreOnlyCountriesOption ? allCountries : this.countries;
                    for (var i = 0; i < countryList.length; i++) {
                        if (countryList[i].iso2 === countryCode) {
                            return countryList[i];
                        }
                    }
                    if (allowFail) {
                        return null;
                    }
                    throw new Error("No country data for '".concat(countryCode, "'"));
                }
            }, {
                key: "_setFlag",
                value: function _setFlag(countryCode) {
                    var prevCountry = this.selectedCountryData.iso2 ? this.selectedCountryData : {};
                    // do this first as it will throw an error and stop if countryCode is invalid
                    this.selectedCountryData = countryCode ? this._getCountryData(countryCode, false, false) : {};
                    // update the defaultCountry - we only need the iso2 from now on, so just store that
                    if (this.selectedCountryData.iso2) {
                        this.defaultCountry = this.selectedCountryData.iso2;
                    }
                    this.selectedFlagInner.setAttribute("class", "iti__flag iti__".concat(countryCode));
                    // update the selected country's title attribute
                    var title = countryCode ? "".concat(this.selectedCountryData.name, ": +").concat(this.selectedCountryData.dialCode) : "Unknown";
                    this.selectedFlag.setAttribute("title", title);
                    if (this.options.separateDialCode) {
                        var dialCode = this.selectedCountryData.dialCode ? "+".concat(this.selectedCountryData.dialCode) : "";
                        this.selectedDialCode.innerHTML = dialCode;
                        // offsetWidth is zero if input is in a hidden container during initialisation
                        var selectedFlagWidth = this.selectedFlag.offsetWidth || this._getHiddenSelectedFlagWidth();
                        // add 6px of padding after the grey selected-dial-code box, as this is what we use in the css
                        this.telInput.style.paddingLeft = "".concat(selectedFlagWidth + 6, "px");
                    }
                    // and the input's placeholder
                    this._updatePlaceholder();
                    // update the active list item
                    if (this.options.allowDropdown) {
                        var prevItem = this.activeItem;
                        if (prevItem) {
                            prevItem.classList.remove("iti__active");
                            prevItem.setAttribute("aria-selected", "false");
                        }
                        if (countryCode) {
                            var nextItem = this.countryList.querySelector("#iti-item-".concat(countryCode));
                            nextItem.setAttribute("aria-selected", "true");
                            nextItem.classList.add("iti__active");
                            this.activeItem = nextItem;
                            this.countryList.setAttribute("aria-activedescendant", nextItem.getAttribute("id"));
                        }
                    }
                    // return if the flag has changed or not
                    return prevCountry.iso2 !== countryCode;
                }
            }, {
                key: "_getHiddenSelectedFlagWidth",
                value: function _getHiddenSelectedFlagWidth() {
                    // to get the right styling to apply, all we need is a shallow clone of the container,
                    // and then to inject a deep clone of the selectedFlag element
                    var containerClone = this.telInput.parentNode.cloneNode();
                    containerClone.style.visibility = "hidden";
                    document.body.appendChild(containerClone);
                    var selectedFlagClone = this.selectedFlag.cloneNode(true);
                    containerClone.appendChild(selectedFlagClone);
                    var width = selectedFlagClone.offsetWidth;
                    containerClone.remove();
                    return width;
                }
            }, {
                key: "_updatePlaceholder",
                value: function _updatePlaceholder() {
                    var shouldSetPlaceholder = this.options.autoPlaceholder === "aggressive" || !this.hadInitialPlaceholder && this.options.autoPlaceholder === "polite";
                    if (window.intlTelInputUtils && shouldSetPlaceholder) {
                        var numberType = intlTelInputUtils.numberType[this.options.placeholderNumberType];
                        var placeholder = this.selectedCountryData.iso2 ? intlTelInputUtils.getExampleNumber(this.selectedCountryData.iso2, this.options.nationalMode, numberType) : "";
                        placeholder = this._beforeSetNumber(placeholder);
                        if (typeof this.options.customPlaceholder === "function") {
                            placeholder = this.options.customPlaceholder(placeholder, this.selectedCountryData);
                        }
                        this.telInput.setAttribute("placeholder", placeholder);
                    }
                }
            }, {
                key: "_selectListItem",
                value: function _selectListItem(listItem) {
                    // update selected flag and active list item
                    var flagChanged = this._setFlag(listItem.getAttribute("data-country-code"));
                    this._closeDropdown();
                    this._updateDialCode(listItem.getAttribute("data-dial-code"), true);
                    // focus the input
                    this.telInput.focus();
                    // put cursor at end - this fix is required for FF and IE11 (with nationalMode=false i.e. auto
                    // inserting dial code), who try to put the cursor at the beginning the first time
                    var len = this.telInput.value.length;
                    this.telInput.setSelectionRange(len, len);
                    if (flagChanged) {
                        this._triggerCountryChange();
                    }
                }
            }, {
                key: "_closeDropdown",
                value: function _closeDropdown() {
                    this.countryList.classList.add("iti__hide");
                    this.countryList.setAttribute("aria-expanded", "false");
                    // update the arrow					
                    this.dropdownArrow.classList.remove("icon-arrow-up");
					this.dropdownArrow.classList.add("icon-arrow-down");
					// unbind key events
                    document.removeEventListener("keydown", this._handleKeydownOnDropdown);
                    document.documentElement.removeEventListener("click", this._handleClickOffToClose);
                    this.countryList.removeEventListener("mouseover", this._handleMouseoverCountryList);
                    this.countryList.removeEventListener("click", this._handleClickCountryList);
                    // remove menu from container
                    if (this.options.dropdownContainer) {
                        if (!this.isMobile) window.removeEventListener("scroll", this._handleWindowScroll);
                        if (this.dropdown.parentNode) this.dropdown.parentNode.removeChild(this.dropdown);
                    }
                    this._trigger("close:countrydropdown");
                }
            }, {
                key: "_scrollTo",
                value: function _scrollTo(element, middle) {
                    var container = this.countryList;
                    // windowTop from https://stackoverflow.com/a/14384091/217866
                    var windowTop = window.pageYOffset || document.documentElement.scrollTop;
                    var containerHeight = container.offsetHeight;
                    var containerTop = container.getBoundingClientRect().top + windowTop;
                    var containerBottom = containerTop + containerHeight;
                    var elementHeight = element.offsetHeight;
                    var elementTop = element.getBoundingClientRect().top + windowTop;
                    var elementBottom = elementTop + elementHeight;
                    var newScrollTop = elementTop - containerTop + container.scrollTop;
                    var middleOffset = containerHeight / 2 - elementHeight / 2;
                    if (elementTop < containerTop) {
                        // scroll up
                        if (middle) newScrollTop -= middleOffset;
                        container.scrollTop = newScrollTop;
                    } else if (elementBottom > containerBottom) {
                        // scroll down
                        if (middle) newScrollTop += middleOffset;
                        var heightDifference = containerHeight - elementHeight;
                        container.scrollTop = newScrollTop - heightDifference;
                    }
                }
            }, {
                key: "_updateDialCode",
                value: function _updateDialCode(newDialCodeBare, hasSelectedListItem) {
                    var inputVal = this.telInput.value;
                    // save having to pass this every time
                    var newDialCode = "+".concat(newDialCodeBare);
                    var newNumber;
                    if (inputVal.charAt(0) === "+") {
                        // there's a plus so we're dealing with a replacement (doesn't matter if nationalMode or not)
                        var prevDialCode = this._getDialCode(inputVal);
                        if (prevDialCode) {
                            // current number contains a valid dial code, so replace it
                            newNumber = inputVal.replace(prevDialCode, newDialCode);
                        } else {
                            // current number contains an invalid dial code, so ditch it
                            // (no way to determine where the invalid dial code ends and the rest of the number begins)
                            newNumber = newDialCode;
                        }
                    } else if (this.options.nationalMode || this.options.separateDialCode) {
                        // don't do anything
                        return;
                    } else {
                        // nationalMode is disabled
                        if (inputVal) {
                            // there is an existing value with no dial code: prefix the new dial code
                            newNumber = newDialCode + inputVal;
                        } else if (hasSelectedListItem || !this.options.autoHideDialCode) {
                            // no existing value and either they've just selected a list item, or autoHideDialCode is
                            // disabled: insert new dial code
                            newNumber = newDialCode;
                        } else {
                            return;
                        }
                    }
                    this.telInput.value = newNumber;
                }
            }, {
                key: "_getDialCode",
                value: function _getDialCode(number) {
                    var dialCode = "";
                    // only interested in international numbers (starting with a plus)
                    if (number.charAt(0) === "+") {
                        var numericChars = "";
                        // iterate over chars
                        for (var i = 0; i < number.length; i++) {
                            var c = number.charAt(i);
                            // if char is number (https://stackoverflow.com/a/8935649/217866)
                            if (!isNaN(parseInt(c, 10))) {
                                numericChars += c;
                                // if current numericChars make a valid dial code
                                if (this.countryCodes[numericChars]) {
                                    // store the actual raw string (useful for matching later)
                                    dialCode = number.substr(0, i + 1);
                                }
                                if (numericChars.length === this.dialCodeMaxLen) {
                                    break;
                                }
                            }
                        }
                    }
                    return dialCode;
                }
            }, {
                key: "_getFullNumber",
                value: function _getFullNumber() {
                    var val = this.telInput.value.trim();
                    var dialCode = this.selectedCountryData.dialCode;
                    var prefix;
                    var numericVal = this._getNumeric(val);
                    if (this.options.separateDialCode && val.charAt(0) !== "+" && dialCode && numericVal) {
                        // when using separateDialCode, it is visible so is effectively part of the typed number
                        prefix = "+".concat(dialCode);
                    } else {
                        prefix = "";
                    }
                    return prefix + val;
                }
            }, {
                key: "_beforeSetNumber",
                value: function _beforeSetNumber(originalNumber) {
                    var number = originalNumber;
                    if (this.options.separateDialCode) {
                        var dialCode = this._getDialCode(number);
                        // if there is a valid dial code
                        if (dialCode) {
                            // in case _getDialCode returned an area code as well
                            dialCode = "+".concat(this.selectedCountryData.dialCode);
                            // a lot of numbers will have a space separating the dial code and the main number, and
                            // some NANP numbers will have a hyphen e.g. +1 684-733-1234 - in both cases we want to get
                            // rid of it
                            // NOTE: don't just trim all non-numerics as may want to preserve an open parenthesis etc
                            var start = number[dialCode.length] === " " || number[dialCode.length] === "-" ? dialCode.length + 1 : dialCode.length;
                            number = number.substr(start);
                        }
                    }
                    return this._cap(number);
                }
            }, {
                key: "_triggerCountryChange",
                value: function _triggerCountryChange() {
                    this._trigger("countrychange");
                }
            }, {
                key: "handleAutoCountry",
                value: function handleAutoCountry() {
                    if (this.options.initialCountry === "auto") {
                        // we must set this even if there is an initial val in the input: in case the initial val is
                        // invalid and they delete it - they should see their auto country
                        this.defaultCountry = window.intlTelInputGlobals.autoCountry;
                        // if there's no initial value in the input, then update the flag
                        if (!this.telInput.value) {
                            this.setCountry(this.defaultCountry);
                        }
                        this.resolveAutoCountryPromise();
                    }
                }
            }, {
                key: "handleUtils",
                value: function handleUtils() {
                    // if the request was successful
                    if (window.intlTelInputUtils) {
                        // if there's an initial value in the input, then format it
                        if (this.telInput.value) {
                            this._updateValFromNumber(this.telInput.value);
                        }
                        this._updatePlaceholder();
                    }
                    this.resolveUtilsScriptPromise();
                }
            }, {
                key: "destroy",
                value: function destroy() {
                    var form = this.telInput.form;
                    if (this.options.allowDropdown) {
                        // make sure the dropdown is closed (and unbind listeners)
                        this._closeDropdown();
                        this.selectedFlag.removeEventListener("click", this._handleClickSelectedFlag);
                        this.flagsContainer.removeEventListener("keydown", this._handleFlagsContainerKeydown);
                        // label click hack
                        var label = this._getClosestLabel();
                        if (label) label.removeEventListener("click", this._handleLabelClick);
                    }
                    // unbind hiddenInput listeners
                    if (this.hiddenInput && form) form.removeEventListener("submit", this._handleHiddenInputSubmit);
                    // unbind autoHideDialCode listeners
                    if (this.options.autoHideDialCode) {
                        if (form) form.removeEventListener("submit", this._handleSubmitOrBlurEvent);
                        this.telInput.removeEventListener("blur", this._handleSubmitOrBlurEvent);
                    }
                    // unbind key events, and cut/paste events
                    this.telInput.removeEventListener("keyup", this._handleKeyupEvent);
                    this.telInput.removeEventListener("cut", this._handleClipboardEvent);
                    this.telInput.removeEventListener("paste", this._handleClipboardEvent);
                    // remove attribute of id instance: data-intl-tel-input-id
                    this.telInput.removeAttribute("data-intl-tel-input-id");
                    // remove markup (but leave the original input)
                    var wrapper = this.telInput.parentNode;
                    wrapper.parentNode.insertBefore(this.telInput, wrapper);
                    wrapper.parentNode.removeChild(wrapper);
                    delete window.intlTelInputGlobals.instances[this.id];
                }
            }, {
                key: "getExtension",
                value: function getExtension() {
                    if (window.intlTelInputUtils) {
                        return intlTelInputUtils.getExtension(this._getFullNumber(), this.selectedCountryData.iso2);
                    }
                    return "";
                }
            }, {
                key: "getNumber",
                value: function getNumber(format) {
                    if (window.intlTelInputUtils) {
                        var iso2 = this.selectedCountryData.iso2;
                        return intlTelInputUtils.formatNumber(this._getFullNumber(), iso2, format);
                    }
                    return "";
                }
            }, {
                key: "getNumberType",
                value: function getNumberType() {
                    if (window.intlTelInputUtils) {
                        return intlTelInputUtils.getNumberType(this._getFullNumber(), this.selectedCountryData.iso2);
                    }
                    return -99;
                }
            }, {
                key: "getSelectedCountryData",
                value: function getSelectedCountryData() {
                    return this.selectedCountryData;
                }
            }, {
                key: "getValidationError",
                value: function getValidationError() {
                    if (window.intlTelInputUtils) {
                        var iso2 = this.selectedCountryData.iso2;
                        return intlTelInputUtils.getValidationError(this._getFullNumber(), iso2);
                    }
                    return -99;
                }
            }, {
                key: "isValidNumber",
                value: function isValidNumber() {
                    var val = this._getFullNumber().trim();
                    var countryCode = this.options.nationalMode ? this.selectedCountryData.iso2 : "";
                    return window.intlTelInputUtils ? intlTelInputUtils.isValidNumber(val, countryCode) : null;
                }
            }, {
                key: "setCountry",
                value: function setCountry(originalCountryCode) {
                    var countryCode = originalCountryCode.toLowerCase();
                    // check if already selected
                    if (!this.selectedFlagInner.classList.contains("iti__".concat(countryCode))) {
                        this._setFlag(countryCode);
                        this._updateDialCode(this.selectedCountryData.dialCode, false);
                        this._triggerCountryChange();
                    }
                }
            }, {
                key: "setNumber",
                value: function setNumber(number) {
                    // we must update the flag first, which updates this.selectedCountryData, which is used for
                    // formatting the number before displaying it
                    var flagChanged = this._updateFlagFromNumber(number);
                    this._updateValFromNumber(number);
                    if (flagChanged) {
                        this._triggerCountryChange();
                    }
                }
            }, {
                key: "setPlaceholderNumberType",
                value: function setPlaceholderNumberType(type) {
                    this.options.placeholderNumberType = type;
                    this._updatePlaceholder();
                }
            } ]);
            return Iti;
        }();
        /********************
 *  STATIC METHODS
 ********************/
        // get the country data object
        window.intlTelInputGlobals.getCountryData = function() {
            return allCountries;
        };
        // inject a <script> element to load utils.js
        var injectScript = function injectScript(path, handleSuccess, handleFailure) {
            // inject a new script element into the page
            var script = document.createElement("script");
            script.onload = function() {
                forEachInstance("handleUtils");
                if (handleSuccess) handleSuccess();
            };
            script.onerror = function() {
                forEachInstance("rejectUtilsScriptPromise");
                if (handleFailure) handleFailure();
            };
            script.className = "iti-load-utils";
            script.async = true;
            script.src = path;
            document.body.appendChild(script);
        };
        // load the utils script
        window.intlTelInputGlobals.loadUtils = function(path) {
            // 2 options:
            // 1) not already started loading (start)
            // 2) already started loading (do nothing - just wait for the onload callback to fire, which will
            // trigger handleUtils on all instances, invoking their resolveUtilsScriptPromise functions)
            if (!window.intlTelInputUtils && !window.intlTelInputGlobals.startedLoadingUtilsScript) {
                // only do this once
                window.intlTelInputGlobals.startedLoadingUtilsScript = true;
                // if we have promises, then return a promise
                if (typeof Promise !== "undefined") {
                    return new Promise(function(resolve, reject) {
                        return injectScript(path, resolve, reject);
                    });
                }
                injectScript(path);
            }
            return null;
        };
        // default options
        window.intlTelInputGlobals.defaults = defaults;
        // version
        window.intlTelInputGlobals.version = "16.0.0";
        // convenience wrapper
        return function(input, options) {
            var iti = new Iti(input, options);
            iti._init();
            input.setAttribute("data-intl-tel-input-id", iti.id);
            window.intlTelInputGlobals.instances[iti.id] = iti;
            return iti;
        };
    }();
});