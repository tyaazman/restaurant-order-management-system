/**
 * Restaurant Order System — Shared Data Store (Demo)
 * All pages import this file.  State is persisted in sessionStorage so
 * changes made on one page (e.g. manage_orders) are visible on others.
 */

window.ROS = window.ROS || {};

// ─────────────────────────────────────────────
//  CATEGORY DISPLAY ORDER
// ─────────────────────────────────────────────
ROS.CATEGORIES = [
  // SIGNATURE
  { id: 'Sup ZZ',          label: 'Sup ZZ',          section: 'SIGNATURE'       },
  { id: 'Mee Rebus ZZ',    label: 'Mee Rebus ZZ',    section: 'SIGNATURE'       },
  // MENU IKAN
  { id: 'Ikan Siakap',     label: 'Ikan Siakap',     section: 'MENU IKAN'       },
  { id: 'Bakar-Bakar',     label: 'Bakar-Bakar',     section: 'MENU IKAN'       },
  // SARAPAN
  { id: 'Masakan Panas',   label: 'Masakan Panas',   section: 'SARAPAN'         },
  { id: 'Roti Bakar',      label: 'Roti Bakar',      section: 'SARAPAN'         },
  // SET TENGAH HARI
  { id: 'Set Nasi & Lauk', label: 'Set Nasi & Lauk', section: 'SET TENGAH HARI' },
  // ROTI CANAI
  { id: 'Roti Canai',      label: 'Roti Canai',      section: 'ROTI CANAI'      },
  // GORENG-GORENG
  { id: 'Nasi Goreng',     label: 'Nasi Goreng',     section: 'GORENG-GORENG'   },
  { id: 'Mee Goreng',      label: 'Mee Goreng',      section: 'GORENG-GORENG'   },
  // ALA-CARTE
  { id: 'Sup Ala Thai',    label: 'Sup Ala Thai',    section: 'ALA-CARTE'       },
  { id: 'Tomyam',          label: 'Tomyam',          section: 'ALA-CARTE'       },
  { id: 'Mee Kuah',        label: 'Mee Kuah',        section: 'ALA-CARTE'       },
  { id: 'Sayur',           label: 'Sayur',           section: 'ALA-CARTE'       },
  { id: 'Aneka Lauk Thai', label: 'Aneka Lauk Thai', section: 'ALA-CARTE'       },
  { id: 'Goreng Tepung',   label: 'Goreng Tepung',   section: 'ALA-CARTE'       },
  // WESTERN
  { id: 'Fried & Grill',   label: 'Fried & Grill',   section: 'WESTERN'         },
  { id: 'Spaghetti',       label: 'Spaghetti',       section: 'WESTERN'         },
  { id: 'Burger',          label: 'Burger',          section: 'WESTERN'         },
  { id: 'Sides',           label: 'Sides',           section: 'WESTERN'         },
  // MINUMAN
  { id: 'Non-Coffee',      label: 'Non-Coffee',      section: 'MINUMAN'         },
  { id: 'Coffee',          label: 'Coffee',          section: 'MINUMAN'         },
  { id: 'Jus',             label: 'Jus',             section: 'MINUMAN'         },
  { id: 'Cold Dessert',    label: 'Cold Dessert',    section: 'MINUMAN'         },
  // LAIN-LAIN
  { id: 'Lain-Lain',       label: 'Lain-Lain',       section: 'LAIN-LAIN'       },
];

// ─────────────────────────────────────────────
//  MENU ITEMS  (from real restaurant menu)
// ─────────────────────────────────────────────
ROS.MENU_DEFAULT = [

  // ── SIGNATURE: Sup ZZ ─────────────────────
  { id: 1,  name: 'Sup Gearbox Kambing',           price: 19.00, category: 'Sup ZZ' },
  { id: 2,  name: 'Sup Kambing',                   price: 20.00, category: 'Sup ZZ' },
  { id: 3,  name: 'Sup Daging',                    price: 8.00,  category: 'Sup ZZ' },
  { id: 4,  name: 'Sup Ayam',                      price: 7.00,  category: 'Sup ZZ' },
  { id: 5,  name: 'Add On (Mee/Mee Hoon/Kuey Teow)', price: 2.00, category: 'Sup ZZ' },
  { id: 6,  name: 'Add On Set Nasi',               price: 5.00,  category: 'Sup ZZ' },
  { id: 7,  name: 'Add On Roti Francis/Gardenia',  price: 2.50,  category: 'Sup ZZ' },

  // ── SIGNATURE: Mee Rebus ZZ ───────────────
  { id: 8,  name: 'Mee Rebus Gearbox Kambing',     price: 20.00, category: 'Mee Rebus ZZ' },
  { id: 9,  name: 'Mee Rebus Daging',              price: 9.50,  category: 'Mee Rebus ZZ' },
  { id: 10, name: 'Mee Rebus Ayam',                price: 9.00,  category: 'Mee Rebus ZZ' },

  // ── MENU IKAN: Ikan Siakap ────────────────
  { id: 11, name: 'Ikan Siakap Tiga Rasa',         price: 35.00, category: 'Ikan Siakap' },
  { id: 12, name: 'Ikan Siakap Masam Manis',       price: 35.00, category: 'Ikan Siakap' },
  { id: 13, name: 'Ikan Siakap Steam Lemon',       price: 35.00, category: 'Ikan Siakap' },
  { id: 14, name: 'Ikan Siakap Laprik',            price: 35.00, category: 'Ikan Siakap' },
  { id: 15, name: 'Ikan Siakap Goreng Kunyit',     price: 35.00, category: 'Ikan Siakap' },

  // ── MENU IKAN: Bakar-Bakar ────────────────
  { id: 16, name: 'Siakap Bakar',                  price: 35.00, category: 'Bakar-Bakar' },
  { id: 17, name: 'Caru Bakar',                    price: 8.00,  category: 'Bakar-Bakar' },
  { id: 18, name: 'Kerang Bakar',                  price: 15.00, category: 'Bakar-Bakar' },
  { id: 19, name: 'Sotong Bakar',                  price: 15.00, category: 'Bakar-Bakar' },
  { id: 20, name: 'Ikan Bakar Sambal',             price: 15.00, category: 'Bakar-Bakar' },

  // ── SARAPAN: Masakan Panas ────────────────
  { id: 21, name: 'Lontong Kuah',                  price: 7.50,  category: 'Masakan Panas' },
  { id: 22, name: 'Lontong Kering (Ayam)',          price: 9.00,  category: 'Masakan Panas' },
  { id: 23, name: 'Lontong Kering (Daging)',        price: 9.50,  category: 'Masakan Panas' },
  { id: 24, name: 'Nasi Lemak Basmathi (Telur)',    price: 6.00,  category: 'Masakan Panas' },
  { id: 25, name: 'Nasi Lemak Basmathi (Ayam)',     price: 9.00,  category: 'Masakan Panas' },
  { id: 26, name: 'Nasi Lemak Rendang (Ayam)',      price: 8.50,  category: 'Masakan Panas' },
  { id: 27, name: 'Nasi Lemak Rendang (Daging)',    price: 9.50,  category: 'Masakan Panas' },
  { id: 28, name: 'Nasi Ayam Basmathi',             price: 12.00, category: 'Masakan Panas' },
  { id: 29, name: 'Nasi Ambang',                    price: 9.50,  category: 'Masakan Panas' },
  { id: 30, name: 'Bubur Nasi',                     price: 7.50,  category: 'Masakan Panas' },
  { id: 31, name: 'Bubur Ayam',                     price: 7.00,  category: 'Masakan Panas' },
  { id: 32, name: 'Laksa (Johor)',                  price: 8.00,  category: 'Masakan Panas' },
  { id: 33, name: 'Laksa (Penang)',                 price: 7.50,  category: 'Masakan Panas' },
  { id: 34, name: 'Bakso (Mee/Mee Hoon/Nasi)',      price: 7.50,  category: 'Masakan Panas' },
  { id: 35, name: 'Soto (Mee/Mee Hoon/Nasi)',       price: 8.00,  category: 'Masakan Panas' },

  // ── SARAPAN: Roti Bakar ───────────────────
  { id: 36, name: 'Roti Bakar',                    price: 2.50,  category: 'Roti Bakar' },
  { id: 37, name: 'Roti Kaya',                     price: 3.50,  category: 'Roti Bakar' },
  { id: 38, name: 'Roti Garlic',                   price: 3.50,  category: 'Roti Bakar' },
  { id: 39, name: 'Add On Telur 1/2 Masak',        price: 3.50,  category: 'Roti Bakar' },

  // ── SET TENGAH HARI: Set Nasi & Lauk ─────
  { id: 40, name: 'Nasi Bawal Goreng Berlado',      price: 9.00,  category: 'Set Nasi & Lauk' },
  { id: 41, name: 'Nasi Siakap Goreng Berlado',     price: 15.00, category: 'Set Nasi & Lauk' },
  { id: 42, name: 'Nasi Keli Goreng Berlado',       price: 10.90, category: 'Set Nasi & Lauk' },
  { id: 43, name: 'Nasi Ayam Goreng Berlado',       price: 8.50,  category: 'Set Nasi & Lauk' },

  // ── ROTI CANAI ───────────────────────────
  { id: 44, name: 'Roti Kosong',                   price: 1.50,  category: 'Roti Canai' },
  { id: 45, name: 'Roti Kosong Bawang',            price: 2.00,  category: 'Roti Canai' },
  { id: 46, name: 'Roti Tampal',                   price: 2.80,  category: 'Roti Canai' },
  { id: 47, name: 'Roti Telur',                    price: 2.80,  category: 'Roti Canai' },
  { id: 48, name: 'Roti Telur Bawang',             price: 3.50,  category: 'Roti Canai' },
  { id: 49, name: 'Roti Telur Double Jantan',       price: 5.50,  category: 'Roti Canai' },
  { id: 50, name: 'Roti Pisang',                   price: 4.50,  category: 'Roti Canai' },
  { id: 51, name: 'Roti Sardin',                   price: 6.00,  category: 'Roti Canai' },
  { id: 52, name: 'Roti Bom',                      price: 2.50,  category: 'Roti Canai' },
  { id: 53, name: 'Roti Planta',                   price: 3.00,  category: 'Roti Canai' },
  { id: 54, name: 'Roti Sarang Burung Daging',     price: 8.00,  category: 'Roti Canai' },
  { id: 55, name: 'Roti Canai',                    price: 1.50,  category: 'Roti Canai' },

  // ── MINUMAN (updated to specific subcategories) ─────────────
  { id: 56, name: 'Teh Tarik',                     price: 2.50,  category: 'Non-Coffee' },
  { id: 57, name: 'Es Kacang',                     price: 5.00,  category: 'Cold Dessert' },

  // ── GORENG-GORENG: Nasi Goreng ───────────
  { id: 58, name: 'Nasi Goreng Kampung',            price: 8.00,  category: 'Nasi Goreng' },

  // ── LAIN-LAIN ─────────────────────────────
  { id: 59, name: 'Ayam Goreng Berempah',           price: 12.00, category: 'Lain-Lain' },

  // ═══════════════════════════════════════════
  //  NEW ITEMS FROM MENU (Images batch 2)
  // ═══════════════════════════════════════════

  // ── GORENG-GORENG: Nasi Goreng (continued) ─
  { id: 60, name: 'Nasi Goreng Biasa',              price: 7.50,  category: 'Nasi Goreng' },
  { id: 61, name: 'Nasi Goreng Cina',               price: 7.50,  category: 'Nasi Goreng' },
  { id: 62, name: 'Nasi Goreng Ikan Masin',         price: 8.50,  category: 'Nasi Goreng' },
  { id: 63, name: 'Nasi Goreng Cili Padi',          price: 8.50,  category: 'Nasi Goreng' },
  { id: 64, name: 'Nasi Goreng Pattaya',            price: 8.50,  category: 'Nasi Goreng' },
  { id: 65, name: 'Nasi Goreng Tom Yam',            price: 9.00,  category: 'Nasi Goreng' },
  { id: 66, name: 'Nasi Goreng Belacan',            price: 12.00, category: 'Nasi Goreng' },
  { id: 67, name: 'Add On Daging (Nasi Goreng)',    price: 1.00,  category: 'Nasi Goreng' },
  { id: 68, name: 'Add On Udang (Nasi Goreng)',     price: 3.00,  category: 'Nasi Goreng' },
  { id: 69, name: 'Add On Sotong (Nasi Goreng)',    price: 3.00,  category: 'Nasi Goreng' },

  // ── GORENG-GORENG: Mee Goreng ─────────────
  { id: 70, name: 'Mee Goreng',                     price: 7.50,  category: 'Mee Goreng' },
  { id: 71, name: 'Mee Hoon Goreng Singapore',      price: 7.50,  category: 'Mee Goreng' },
  { id: 72, name: 'Char Kuey Teow',                 price: 8.00,  category: 'Mee Goreng' },
  { id: 73, name: 'Add On Daging (Mee Goreng)',     price: 1.00,  category: 'Mee Goreng' },
  { id: 74, name: 'Add On Udang (Mee Goreng)',      price: 3.00,  category: 'Mee Goreng' },
  { id: 75, name: 'Add On Sotong (Mee Goreng)',     price: 3.00,  category: 'Mee Goreng' },

  // ── ALA-CARTE: Sup Ala Thai ───────────────
  { id: 76, name: 'Sup Ayam Ala Thai',              price: 8.00,  category: 'Sup Ala Thai' },
  { id: 77, name: 'Sup Daging Ala Thai',            price: 9.00,  category: 'Sup Ala Thai' },
  { id: 78, name: 'Add On (Mee/Mee Hoon/Kuey Teow) - Sup', price: 2.00, category: 'Sup Ala Thai' },

  // ── ALA-CARTE: Tomyam ─────────────────────
  { id: 79, name: 'Tom Yam Ayam',                   price: 8.00,  category: 'Tomyam' },
  { id: 80, name: 'Tom Yam Daging',                 price: 9.00,  category: 'Tomyam' },
  { id: 81, name: 'Tom Yam Ayam + Daging',          price: 12.00, category: 'Tomyam' },
  { id: 82, name: 'Tom Yam Seafood',                price: 13.00, category: 'Tomyam' },
  { id: 83, name: 'Tom Yam Campur',                 price: 13.00, category: 'Tomyam' },
  { id: 84, name: 'Tom Yam Sayur',                  price: 8.00,  category: 'Tomyam' },
  { id: 85, name: 'Tom Yam Cendawan',               price: 8.00,  category: 'Tomyam' },
  { id: 86, name: 'Add On (Mee/Mee Hoon/Kuey Teow) - Tomyam', price: 2.00, category: 'Tomyam' },

  // ── ALA-CARTE: Mee Kuah ───────────────────
  { id: 87, name: 'Mee Kuah Bandung',               price: 10.50, category: 'Mee Kuah' },
  { id: 88, name: 'Mee Kuah Hong Kong',             price: 10.50, category: 'Mee Kuah' },
  { id: 89, name: 'Mee Kuah Hailam',               price: 10.50, category: 'Mee Kuah' },
  { id: 90, name: 'Mee Kuah Kung Fu',               price: 10.50, category: 'Mee Kuah' },

  // ── ALA-CARTE: Sayur ──────────────────────
  { id: 91, name: 'Kailan (Biasa/Ikan Masin)',      price: 7.00,  category: 'Sayur' },
  { id: 92, name: 'Kangkung (Biasa/Belacan)',       price: 7.00,  category: 'Sayur' },
  { id: 93, name: 'Taugeh (Biasa/Ikan Masin)',      price: 7.00,  category: 'Sayur' },
  { id: 94, name: 'Sawi (Biasa/Ikan Masin)',        price: 7.00,  category: 'Sayur' },
  { id: 95, name: 'Cendawan Goreng Biasa',          price: 7.00,  category: 'Sayur' },

  // ── ALA-CARTE: Aneka Lauk Thai ────────────
  { id: 96,  name: 'Ayam Black Pepper',             price: 7.50,  category: 'Aneka Lauk Thai' },
  { id: 97,  name: 'Daging Black Pepper',           price: 8.50,  category: 'Aneka Lauk Thai' },
  { id: 98,  name: 'Sotong Black Pepper',           price: 9.50,  category: 'Aneka Lauk Thai' },
  { id: 99,  name: 'Ayam Sambal',                   price: 7.50,  category: 'Aneka Lauk Thai' },
  { id: 100, name: 'Daging Sambal',                 price: 8.50,  category: 'Aneka Lauk Thai' },
  { id: 101, name: 'Sotong Sambal',                 price: 9.50,  category: 'Aneka Lauk Thai' },
  { id: 102, name: 'Ayam Merah',                    price: 7.50,  category: 'Aneka Lauk Thai' },
  { id: 103, name: 'Daging Merah',                  price: 8.50,  category: 'Aneka Lauk Thai' },
  { id: 104, name: 'Sotong Merah',                  price: 9.50,  category: 'Aneka Lauk Thai' },
  { id: 105, name: 'Ayam Paprik',                   price: 7.50,  category: 'Aneka Lauk Thai' },
  { id: 106, name: 'Daging Paprik',                 price: 8.50,  category: 'Aneka Lauk Thai' },
  { id: 107, name: 'Sotong Paprik',                 price: 9.50,  category: 'Aneka Lauk Thai' },
  { id: 108, name: 'Ayam Pha Khra Phao',            price: 8.00,  category: 'Aneka Lauk Thai' },
  { id: 109, name: 'Daging Pha Khra Phao',          price: 9.00,  category: 'Aneka Lauk Thai' },
  { id: 110, name: 'Ayam Kunyit',                   price: 7.50,  category: 'Aneka Lauk Thai' },
  { id: 111, name: 'Daging Kunyit',                 price: 9.50,  category: 'Aneka Lauk Thai' },
  { id: 112, name: 'Sotong Kunyit',                 price: 9.50,  category: 'Aneka Lauk Thai' },
  { id: 113, name: 'Udang Kunyit',                  price: 9.50,  category: 'Aneka Lauk Thai' },
  { id: 114, name: 'Add On Nasi Putih (Lauk Thai)', price: 2.00,  category: 'Aneka Lauk Thai' },
  { id: 115, name: 'Add On Nasi Goreng (Lauk Thai)',price: 3.00,  category: 'Aneka Lauk Thai' },

  // ── ALA-CARTE: Goreng Tepung ──────────────
  { id: 116, name: 'Goreng Tepung Sotong',          price: 10.50, category: 'Goreng Tepung' },
  { id: 117, name: 'Goreng Tepung Udang',           price: 10.50, category: 'Goreng Tepung' },
  { id: 118, name: 'Goreng Tepung Cendawan',        price: 7.00,  category: 'Goreng Tepung' },
  { id: 119, name: 'Goreng Tepung Inokki',          price: 7.00,  category: 'Goreng Tepung' },

  // ── WESTERN: Fried & Grill ────────────────
  { id: 120, name: 'Chicken Chop (Fried/Grill)',    price: 18.50, category: 'Fried & Grill' },
  { id: 121, name: 'Fish N Chips',                  price: 16.50, category: 'Fried & Grill' },
  { id: 122, name: 'Lamb Chop',                     price: 30.90, category: 'Fried & Grill' },

  // ── WESTERN: Spaghetti ────────────────────
  { id: 123, name: 'Aglio Olio (Chicken)',          price: 13.00, category: 'Spaghetti' },
  { id: 124, name: 'Aglio Olio (Beef Bacon)',       price: 15.00, category: 'Spaghetti' },
  { id: 125, name: 'Aglio Olio (Seafood)',          price: 17.00, category: 'Spaghetti' },
  { id: 126, name: 'Carbonara (Chicken)',           price: 14.00, category: 'Spaghetti' },
  { id: 127, name: 'Carbonara (Beef Bacon)',        price: 16.00, category: 'Spaghetti' },
  { id: 128, name: 'Carbonara (Seafood)',           price: 18.00, category: 'Spaghetti' },
  { id: 129, name: 'Bolognese',                     price: 15.00, category: 'Spaghetti' },
  { id: 130, name: 'Mac & Cheese',                  price: 12.00, category: 'Spaghetti' },

  // ── WESTERN: Burger ───────────────────────
  { id: 131, name: 'Smash Beef Single',             price: 8.00,  category: 'Burger' },
  { id: 132, name: 'Smash Beef Double',             price: 10.00, category: 'Burger' },
  { id: 133, name: 'Crispy Chicken Burger',         price: 7.50,  category: 'Burger' },
  { id: 134, name: 'Add On: Fries (Burger)',        price: 2.00,  category: 'Burger' },

  // ── WESTERN: Sides ────────────────────────
  { id: 135, name: 'Fries',                         price: 7.50,  category: 'Sides' },
  { id: 136, name: 'Nugget 8pcs',                   price: 8.00,  category: 'Sides' },
  { id: 137, name: 'Cheesy Wedges',                 price: 8.50,  category: 'Sides' },

  // ── MINUMAN: Non-Coffee ───────────────────
  { id: 138, name: "Teh O'",                        price: 2.30,  category: 'Non-Coffee' },
  { id: 139, name: 'Teh Halia',                     price: 3.50,  category: 'Non-Coffee' },
  { id: 140, name: 'Teh Sarbat',                    price: 3.50,  category: 'Non-Coffee' },
  { id: 141, name: 'Sirap',                         price: 2.00,  category: 'Non-Coffee' },
  { id: 142, name: 'Sirap Selasih',                 price: 2.50,  category: 'Non-Coffee' },
  { id: 143, name: 'Sirap Limau',                   price: 2.70,  category: 'Non-Coffee' },
  { id: 144, name: 'Sirap Laici',                   price: 5.00,  category: 'Non-Coffee' },
  { id: 145, name: 'Sirap Bandung (Iced)',          price: 3.50,  category: 'Non-Coffee' },
  { id: 146, name: 'Sirap Bandung Cincau (Iced)',   price: 4.00,  category: 'Non-Coffee' },
  { id: 147, name: 'Sirap Bandung Soda (Iced)',     price: 4.00,  category: 'Non-Coffee' },
  { id: 148, name: 'Limau',                         price: 2.70,  category: 'Non-Coffee' },
  { id: 149, name: 'Asam Boy',                      price: 2.70,  category: 'Non-Coffee' },
  { id: 150, name: 'Extra Joss Susu (Iced)',        price: 4.00,  category: 'Non-Coffee' },
  { id: 151, name: 'Vico',                          price: 3.00,  category: 'Non-Coffee' },

  // ── MINUMAN: Coffee ───────────────────────
  { id: 152, name: "Indo Cafe O'",                  price: 3.00,  category: 'Coffee' },
  { id: 153, name: 'Indo Cafe Susu',               price: 3.50,  category: 'Coffee' },
  { id: 154, name: 'Kopi Tenggek',                 price: 3.50,  category: 'Coffee' },
  { id: 155, name: 'Kopi Special',                 price: 4.00,  category: 'Coffee' },

  // ── MINUMAN: Jus ──────────────────────────
  { id: 156, name: 'Jus Oren',                     price: 5.00,  category: 'Jus' },
  { id: 157, name: 'Jus Epal',                     price: 5.00,  category: 'Jus' },
  { id: 158, name: 'Jus Tembikai',                 price: 5.00,  category: 'Jus' },
  { id: 159, name: 'Jus Laici',                    price: 5.00,  category: 'Jus' },
  { id: 160, name: 'Jus Lemon',                    price: 5.00,  category: 'Jus' },

  // ── MINUMAN: Cold Dessert ─────────────────
  { id: 161, name: 'Cikong',                       price: 6.00,  category: 'Cold Dessert' },
  { id: 162, name: 'Ais Jelly Limau',              price: 6.00,  category: 'Cold Dessert' },
  { id: 163, name: 'Cendol',                       price: 6.00,  category: 'Cold Dessert' },
];

// ─────────────────────────────────────────────
//  ORDERS  (keyed by date string YYYY-MM-DD)
// ─────────────────────────────────────────────
ROS.ORDERS_DEFAULT = {

  /* ── TODAY: mix of active statuses ── */
  '2026-06-12': [
    { id: 1001, customer: 'Walk-in (Table 4)', time: '12:05 PM',
      items: [
        { id: 'a1', name: 'Sup Gearbox Kambing', qty: 1, status: 'pending'    },
        { id: 'a2', name: 'Mee Rebus Daging',    qty: 1, status: 'pending'    },
      ]
    },
    { id: 1002, customer: 'Online (Ali)', time: '12:22 PM',
      items: [
        { id: 'b1', name: 'Nasi Goreng Kampung', qty: 2, status: 'inprogress' },
        { id: 'b2', name: 'Teh Tarik',           qty: 2, status: 'inprogress' },
      ]
    },
    { id: 1003, customer: 'Walk-in (Table 7)', time: '12:40 PM',
      items: [
        { id: 'c1', name: 'Teh Tarik',    qty: 3, status: 'ready' },
        { id: 'c2', name: 'Roti Canai',   qty: 1, status: 'ready' },
      ]
    },
    { id: 1004, customer: 'Walk-in (Table 2)', time: '11:15 AM',
      items: [
        { id: 'd1', name: 'Sup Ayam',    qty: 1, status: 'completed' },
        { id: 'd2', name: 'Roti Bakar',  qty: 2, status: 'completed' },
      ]
    },
    { id: 1005, customer: 'Online (Siti)', time: '11:50 AM',
      items: [
        { id: 'e1', name: 'Ayam Goreng Berempah', qty: 2, status: 'completed' },
        { id: 'e2', name: 'Es Kacang',            qty: 2, status: 'completed' },
      ]
    },
  ],

  /* ── YESTERDAY: all completed ── */
  '2026-06-11': [
    { id: 994, customer: 'Online (Rahman)', time: '09:15 AM',
      items: [
        { id: 'f1', name: 'Ikan Bakar Sambal',    qty: 1, status: 'completed' },
        { id: 'f2', name: 'Nasi Goreng Kampung',  qty: 2, status: 'completed' },
        { id: 'f3', name: 'Teh Tarik',            qty: 2, status: 'completed' },
      ]
    },
    { id: 995, customer: 'Walk-in (Table 3)', time: '10:00 AM',
      items: [
        { id: 'g1', name: 'Roti Kosong', qty: 3, status: 'completed' },
        { id: 'g2', name: 'Teh Tarik',   qty: 3, status: 'completed' },
      ]
    },
    { id: 996, customer: 'Walk-in (Table 6)', time: '11:30 AM',
      items: [
        { id: 'h1', name: 'Sup Gearbox Kambing',  qty: 2, status: 'completed' },
        { id: 'h2', name: 'Nasi Goreng Kampung',  qty: 2, status: 'completed' },
        { id: 'h3', name: 'Teh Tarik',            qty: 4, status: 'completed' },
      ]
    },
    { id: 997, customer: 'Online (Farah)', time: '12:10 PM',
      items: [
        { id: 'i1', name: 'Ayam Goreng Berempah', qty: 1, status: 'completed' },
        { id: 'i2', name: 'Es Kacang',            qty: 1, status: 'completed' },
      ]
    },
    { id: 998, customer: 'Walk-in (Table 1)', time: '01:00 PM',
      items: [
        { id: 'j1', name: 'Mee Rebus Daging',     qty: 2, status: 'completed' },
        { id: 'j2', name: 'Roti Bakar',           qty: 2, status: 'completed' },
        { id: 'j3', name: 'Teh Tarik',            qty: 2, status: 'completed' },
      ]
    },
    { id: 999, customer: 'Walk-in (Table 8)', time: '02:30 PM',
      items: [
        { id: 'k1', name: 'Ikan Bakar Sambal', qty: 1, status: 'completed' },
        { id: 'k2', name: 'Sup Ayam',          qty: 1, status: 'completed' },
        { id: 'k3', name: 'Teh Tarik',         qty: 2, status: 'completed' },
      ]
    },
    { id: 1000, customer: 'Online (Haziq)', time: '03:45 PM',
      items: [
        { id: 'l1', name: 'Nasi Goreng Kampung', qty: 3, status: 'completed' },
        { id: 'l2', name: 'Teh Tarik',           qty: 3, status: 'completed' },
      ]
    },
  ],

  /* ── 2 DAYS AGO ── */
  '2026-06-10': [
    { id: 986, customer: 'Walk-in (Table 5)', time: '10:20 AM',
      items: [
        { id: 'm1', name: 'Roti Kosong',  qty: 2, status: 'completed' },
        { id: 'm2', name: 'Teh Tarik',    qty: 2, status: 'completed' },
        { id: 'm3', name: 'Es Kacang',    qty: 1, status: 'completed' },
      ]
    },
    { id: 987, customer: 'Online (Azri)', time: '11:00 AM',
      items: [
        { id: 'n1', name: 'Ikan Bakar Sambal',   qty: 2, status: 'completed' },
        { id: 'n2', name: 'Nasi Goreng Kampung', qty: 2, status: 'completed' },
        { id: 'n3', name: 'Teh Tarik',           qty: 2, status: 'completed' },
      ]
    },
    { id: 988, customer: 'Walk-in (Table 3)', time: '12:15 PM',
      items: [
        { id: 'o1', name: 'Sup Gearbox Kambing', qty: 1, status: 'completed' },
        { id: 'o2', name: 'Mee Rebus Daging',    qty: 2, status: 'completed' },
        { id: 'o3', name: 'Teh Tarik',           qty: 3, status: 'completed' },
      ]
    },
    { id: 989, customer: 'Online (Nadia)', time: '01:30 PM',
      items: [
        { id: 'p1', name: 'Ayam Goreng Berempah', qty: 2, status: 'completed' },
        { id: 'p2', name: 'Roti Bakar',           qty: 2, status: 'completed' },
      ]
    },
    { id: 990, customer: 'Walk-in (Table 9)', time: '02:00 PM',
      items: [
        { id: 'q1', name: 'Sup Ayam',    qty: 2, status: 'completed' },
        { id: 'q2', name: 'Roti Kosong', qty: 3, status: 'completed' },
        { id: 'q3', name: 'Teh Tarik',   qty: 3, status: 'completed' },
      ]
    },
    { id: 991, customer: 'Online (Luqman)', time: '03:20 PM',
      items: [
        { id: 'r1', name: 'Nasi Goreng Kampung', qty: 1, status: 'completed' },
        { id: 'r2', name: 'Es Kacang',           qty: 1, status: 'completed' },
      ]
    },
  ],

  /* ── 3 DAYS AGO ── */
  '2026-06-09': [
    { id: 978, customer: 'Walk-in (Table 2)', time: '09:30 AM',
      items: [
        { id: 's1', name: 'Roti Kosong', qty: 4, status: 'completed' },
        { id: 's2', name: 'Teh Tarik',   qty: 4, status: 'completed' },
      ]
    },
    { id: 979, customer: 'Online (Amin)', time: '10:45 AM',
      items: [
        { id: 't1', name: 'Sup Gearbox Kambing', qty: 1, status: 'completed' },
        { id: 't2', name: 'Nasi Goreng Kampung', qty: 1, status: 'completed' },
        { id: 't3', name: 'Teh Tarik',           qty: 2, status: 'completed' },
      ]
    },
    { id: 980, customer: 'Walk-in (Table 4)', time: '11:20 AM',
      items: [
        { id: 'u1', name: 'Mee Rebus Daging', qty: 2, status: 'completed' },
        { id: 'u2', name: 'Roti Bakar',       qty: 1, status: 'completed' },
      ]
    },
    { id: 981, customer: 'Online (Yana)', time: '12:30 PM',
      items: [
        { id: 'v1', name: 'Ikan Bakar Sambal',   qty: 1, status: 'completed' },
        { id: 'v2', name: 'Nasi Goreng Kampung', qty: 1, status: 'completed' },
        { id: 'v3', name: 'Es Kacang',           qty: 2, status: 'completed' },
      ]
    },
    { id: 982, customer: 'Walk-in (Table 6)', time: '01:45 PM',
      items: [
        { id: 'w1', name: 'Ayam Goreng Berempah', qty: 3, status: 'completed' },
        { id: 'w2', name: 'Teh Tarik',            qty: 3, status: 'completed' },
      ]
    },
    { id: 983, customer: 'Walk-in (Table 1)', time: '02:50 PM',
      items: [
        { id: 'x1', name: 'Sup Ayam',    qty: 1, status: 'completed' },
        { id: 'x2', name: 'Roti Kosong', qty: 2, status: 'completed' },
        { id: 'x3', name: 'Teh Tarik',   qty: 2, status: 'completed' },
      ]
    },
    { id: 984, customer: 'Online (Daud)', time: '04:00 PM',
      items: [
        { id: 'y1', name: 'Nasi Goreng Kampung', qty: 2, status: 'completed' },
        { id: 'y2', name: 'Teh Tarik',           qty: 2, status: 'completed' },
      ]
    },
  ],
};

// ─────────────────────────────────────────────
//  SESSION STORAGE HELPERS
// ─────────────────────────────────────────────
ROS.getMenu = function () {
  try {
    const s = sessionStorage.getItem('ros_menu');
    return s ? JSON.parse(s) : JSON.parse(JSON.stringify(ROS.MENU_DEFAULT));
  } catch (e) { return JSON.parse(JSON.stringify(ROS.MENU_DEFAULT)); }
};
ROS.saveMenu = function (menu) {
  sessionStorage.setItem('ros_menu', JSON.stringify(menu));
};

ROS.getOrders = function () {
  try {
    const s = sessionStorage.getItem('ros_orders');
    return s ? JSON.parse(s) : JSON.parse(JSON.stringify(ROS.ORDERS_DEFAULT));
  } catch (e) { return JSON.parse(JSON.stringify(ROS.ORDERS_DEFAULT)); }
};
ROS.saveOrders = function (orders) {
  sessionStorage.setItem('ros_orders', JSON.stringify(orders));
};

// ─────────────────────────────────────────────
//  STATUS HELPERS
// ─────────────────────────────────────────────
ROS.getOverallStatus = function (items) {
  if (!items || items.length === 0) return 'pending';
  if (items.every(i => i.status === 'completed'))  return 'completed';
  if (items.some(i  => i.status === 'ready'))      return 'ready';
  if (items.some(i  => i.status === 'inprogress')) return 'inprogress';
  return 'pending';
};

ROS.STATUS = {
  pending:    { badgeClass: 'badge-pending',    badgeLabel: '⚠ Pending Items', pillClass: 'pill-pending',    pillLabel: 'Pending'     },
  inprogress: { badgeClass: 'badge-inprogress', badgeLabel: '🍳 In Progress',  pillClass: 'pill-inprogress', pillLabel: 'In Progress'  },
  ready:      { badgeClass: 'badge-ready',      badgeLabel: '✅ Ready',         pillClass: 'pill-ready',      pillLabel: 'Ready'       },
  completed:  { badgeClass: 'badge-completed',  badgeLabel: '✔ Completed',     pillClass: 'pill-completed',  pillLabel: 'Completed'   },
};

// ─────────────────────────────────────────────
//  URL PARAM HELPER
// ─────────────────────────────────────────────
ROS.getParam = function (name) {
  return new URLSearchParams(window.location.search).get(name);
};

// ─────────────────────────────────────────────
//  TOAST
// ─────────────────────────────────────────────
ROS.showToast = function (msg) {
  let t = document.getElementById('ros-toast');
  if (!t) {
    t = document.createElement('div');
    t.id = 'ros-toast';
    Object.assign(t.style, {
      position: 'fixed', bottom: '28px', right: '28px',
      background: 'var(--text-brown)', color: '#fff',
      padding: '12px 22px', borderRadius: '8px',
      fontFamily: "'Poppins', sans-serif", fontWeight: '600', fontSize: '0.9rem',
      boxShadow: '0 4px 16px rgba(0,0,0,0.22)',
      zIndex: '9999', transition: 'opacity 0.35s',
    });
    document.body.appendChild(t);
  }
  t.innerText = msg;
  t.style.opacity = '1';
  clearTimeout(ROS._toastTimer);
  ROS._toastTimer = setTimeout(() => { t.style.opacity = '0'; }, 2800);
};
