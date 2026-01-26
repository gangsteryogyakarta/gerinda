<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;

class DiyLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Data kode pos berdasarkan sumber resmi Pos Indonesia
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // Clear existing DIY data to ensure fresh accurate data
            $this->command->info("Clearing existing DIY location data...");
            
            // Get DIY province
            $diyProvince = Province::where('code', '34')->first();
            
            if ($diyProvince) {
                // Delete villages first (child tables)
                DB::table('villages')
                    ->whereIn('district_id', function($q) use ($diyProvince) {
                        $q->select('id')->from('districts')
                            ->whereIn('regency_id', function($q2) use ($diyProvince) {
                                $q2->select('id')->from('regencies')
                                    ->where('province_id', $diyProvince->id);
                            });
                    })
                    ->delete();
                
                // Delete districts
                DB::table('districts')
                    ->whereIn('regency_id', function($q) use ($diyProvince) {
                        $q->select('id')->from('regencies')
                            ->where('province_id', $diyProvince->id);
                    })
                    ->delete();
                
                // Delete regencies
                DB::table('regencies')
                    ->where('province_id', $diyProvince->id)
                    ->delete();
                
                $this->command->info("Existing DIY data cleared.");
            }

            // 1. Create Province: DI YOGYAKARTA
            $province = Province::updateOrCreate(
                ['code' => '34'],
                ['name' => 'DI YOGYAKARTA']
            );

            $this->command->info("Province: {$province->name}");

            // 2. Complete Data with ACCURATE Postal Codes
            $data = $this->getDiyData();

            foreach ($data as $regName => $regData) {
                $regency = Regency::updateOrCreate(
                    ['code' => $regData['code']],
                    [
                        'province_id' => $province->id,
                        'name' => $regName
                    ]
                );

                $this->command->info("  Regency: {$regName}");

                $distCounter = 10;
                foreach ($regData['districts'] as $distName => $villages) {
                    $distCode = $regData['code'] . str_pad($distCounter, 2, '0', STR_PAD_LEFT);
                    $distCounter++;

                    $district = District::updateOrCreate(
                        ['regency_id' => $regency->id, 'name' => $distName],
                        ['code' => $distCode]
                    );

                    $villCounter = 1001;
                    foreach ($villages as $villName => $postalCode) {
                        $villCode = $distCode . str_pad($villCounter, 4, '0', STR_PAD_LEFT);
                        $villCounter++;
                        
                        Village::create([
                            'district_id' => $district->id,
                            'name' => $villName,
                            'code' => $villCode,
                            'postal_code' => $postalCode
                        ]);
                    }
                }
            }

            DB::commit();
            $this->command->info('DIY Location Seeding with Accurate Postal Codes Completed Successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Seeding failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get DIY data with accurate postal codes
     */
    private function getDiyData(): array
    {
        return [
            'KOTA YOGYAKARTA' => [
                'code' => '3471',
                'districts' => [
                    'MANTRIJERON' => [
                        'GEDONGKIWO' => '55142',
                        'SURYODININGRATAN' => '55141',
                        'MANTRIJERON' => '55143',
                    ],
                    'KRATON' => [
                        'PATEHAN' => '55133',
                        'PANEMBAHAN' => '55131',
                        'KADIPATEN' => '55132',
                    ],
                    'MERGANGSAN' => [
                        'BRONTOKUSUMAN' => '55153',
                        'KEPARAKAN' => '55152',
                        'WIROGUNAN' => '55151',
                    ],
                    'UMBULHARJO' => [
                        'PANDEYAN' => '55161',
                        'SOROSUTAN' => '55162',
                        'GIWANGAN' => '55163',
                        'WARUNGBOTO' => '55164',
                        'MUJA MUJU' => '55165',
                        'SEMAKI' => '55166',
                        'TAHUNAN' => '55167',
                    ],
                    'KOTAGEDE' => [
                        'REJOWINANGUN' => '55171',
                        'PRENGGAN' => '55172',
                        'PURBAYAN' => '55173',
                    ],
                    'GONDOKUSUMAN' => [
                        'DEMANGAN' => '55221',
                        'KLITREN' => '55222',
                        'TERBAN' => '55223',
                        'KOTABARU' => '55224',
                        'BACIRO' => '55225',
                    ],
                    'DANUREJAN' => [
                        'BAUSASRAN' => '55211',
                        'TEGAL PANGGUNG' => '55212',
                        'SURYATMAJAN' => '55213',
                    ],
                    'PAKUALAMAN' => [
                        'GUNUNGKETUR' => '55112',
                        'PURWOKINANTI' => '55111',
                    ],
                    'GONDOMANAN' => [
                        'PRAWIRODIRJAN' => '55121',
                        'NGUPASAN' => '55122',
                    ],
                    'NGAMPILAN' => [
                        'NGAMPILAN' => '55261',
                        'NOTOPRAJAN' => '55262',
                    ],
                    'WIROBRAJAN' => [
                        'PAKUNCEN' => '55253',
                        'WIROBRAJAN' => '55252',
                        'PATANGPULUHAN' => '55251',
                    ],
                    'GEDONGTENGEN' => [
                        'PRINGGOKUSUMAN' => '55272',
                        'SOSROMENDURAN' => '55271',
                    ],
                    'JETIS' => [
                        'BUMIJO' => '55231',
                        'GOWONGAN' => '55232',
                        'COKRODININGRATAN' => '55233',
                    ],
                    'TEGALREJO' => [
                        'KARANGWARU' => '55241',
                        'KRICAK' => '55242',
                        'BENER' => '55243',
                        'TEGALREJO' => '55244',
                    ],
                ],
            ],
            'KABUPATEN SLEMAN' => [
                'code' => '3404',
                'districts' => [
                    'GAMPING' => [
                        'BALECATUR' => '55295',
                        'AMBARKETAWANG' => '55294',
                        'BANYURADEN' => '55293',
                        'NOGOTIRTO' => '55292',
                        'TRIHANGGO' => '55291',
                    ],
                    'GODEAN' => [
                        'SIDOKARTO' => '55564',
                        'SIDOMULYO' => '55564',
                        'SIDOMOYO' => '55564',
                        'SIDOARUM' => '55564',
                        'SIDOLUHUR' => '55564',
                        'SIDOAGUNG' => '55564',
                        'SIDOREJO' => '55564',
                    ],
                    'MOYUDAN' => [
                        'SUMBERAHAYU' => '55563',
                        'SUMBERAGUNG' => '55563',
                        'SUMBERSARI' => '55563',
                        'SUMBERARUM' => '55563',
                    ],
                    'MINGGIR' => [
                        'SENDANGAGUNG' => '55562',
                        'SENDANGSARI' => '55562',
                        'SENDANGREJO' => '55562',
                        'SENDANGMULYO' => '55562',
                        'SENDANGARUM' => '55562',
                    ],
                    'SEYEGAN' => [
                        'MARGOLUWIH' => '55561',
                        'MARGODADI' => '55561',
                        'MARGOMULYO' => '55561',
                        'MARGOAGUNG' => '55561',
                        'MARGOKATON' => '55561',
                    ],
                    'MLATI' => [
                        'SINDUADI' => '55284',
                        'SENDANGADI' => '55285',
                        'TLOGOADI' => '55286',
                        'SUMBERADI' => '55288',
                        'TIRTOADI' => '55287',
                    ],
                    'DEPOK' => [
                        'CATURTUNGGAL' => '55281',
                        'MAGUWOHARJO' => '55282',
                        'CONDONGCATUR' => '55283',
                    ],
                    'BERBAH' => [
                        'SENDANGTIRTO' => '55573',
                        'TEGALTIRTO' => '55573',
                        'JOGOTIRTO' => '55573',
                        'KALITIRTO' => '55573',
                    ],
                    'PRAMBANAN' => [
                        'BOKOHARJO' => '55572',
                        'MADUREJO' => '55572',
                        'SUMBERHARJO' => '55572',
                        'WUKIRHARJO' => '55572',
                        'GAYAMHARJO' => '55572',
                        'SAMBIREJO' => '55572',
                    ],
                    'KALASAN' => [
                        'PURWOMARTANI' => '55571',
                        'TIRTOMARTANI' => '55571',
                        'TAMANMARTANI' => '55571',
                        'SELOMARTANI' => '55571',
                    ],
                    'NGEMPLAK' => [
                        'SINDUMARTANI' => '55584',
                        'BIMOMARTANI' => '55584',
                        'WIDODOMARTANI' => '55584',
                        'WEDOMARTANI' => '55584',
                        'UMBULMARTANI' => '55584',
                    ],
                    'NGAGLIK' => [
                        'SARIHARJO' => '55581',
                        'MINOMARTANI' => '55581',
                        'SINDUHARJO' => '55581',
                        'SUKOHARJO' => '55581',
                        'DONOHARJO' => '55581',
                        'SARDONOHARJO' => '55581',
                    ],
                    'SLEMAN' => [
                        'TRIHARJO' => '55514',
                        'CATURHARJO' => '55515',
                        'TRIMULYO' => '55513',
                        'PANDOWOHARJO' => '55512',
                        'TRIDADI' => '55511',
                    ],
                    'TEMPEL' => [
                        'BANYUREJO' => '55552',
                        'TAMBAKREJO' => '55552',
                        'SUMBERREJO' => '55552',
                        'PONDOKREJO' => '55552',
                        'MOROREJO' => '55552',
                        'MARGOREJO' => '55552',
                        'LUMBUNGREJO' => '55552',
                        'MERDIKOREJO' => '55552',
                    ],
                    'TURI' => [
                        'BANGUNKERTO' => '55551',
                        'DONOKERTO' => '55551',
                        'GIRIKERTO' => '55551',
                        'WONOKERTO' => '55551',
                    ],
                    'PAKEM' => [
                        'PURWOBINANGUN' => '55582',
                        'CANDIBINANGUN' => '55582',
                        'HARJOBINANGUN' => '55582',
                        'PAKEMBINANGUN' => '55582',
                        'HARGOBINANGUN' => '55582',
                    ],
                    'CANGKRINGAN' => [
                        'WUKIRSARI' => '55583',
                        'ARGOMULYO' => '55583',
                        'GLAGAHARJO' => '55583',
                        'KEPUHARJO' => '55583',
                        'UMBULHARJO' => '55583',
                    ],
                ],
            ],
            'KABUPATEN BANTUL' => [
                'code' => '3402',
                'districts' => [
                    'SRANDAKAN' => [
                        'PONCOSARI' => '55762',
                        'TRIMURTI' => '55762',
                    ],
                    'SANDEN' => [
                        'GADINGSARI' => '55763',
                        'GADINGHARJO' => '55763',
                        'SRIGADING' => '55763',
                        'MURTIGADING' => '55763',
                    ],
                    'KRETEK' => [
                        'TIRTOMULYO' => '55772',
                        'PARANGTRITIS' => '55772',
                        'DONOTIRTO' => '55772',
                        'TIRTOSARI' => '55772',
                        'TIRTOHARJO' => '55772',
                    ],
                    'PUNDONG' => [
                        'SELOHARJO' => '55771',
                        'PANJANGREJO' => '55771',
                        'SRIHARDONO' => '55771',
                    ],
                    'BAMBANGLIPURO' => [
                        'SUMBERMULYO' => '55764',
                        'MULYODADI' => '55764',
                        'SIDOMULYO' => '55764',
                    ],
                    'PANDAK' => [
                        'CATURHARJO' => '55761',
                        'TRIHARJO' => '55761',
                        'GILANGHARJO' => '55761',
                        'WIJIREJO' => '55761',
                    ],
                    'BANTUL' => [
                        'BANTUL' => '55711',
                        'RINGINHARJO' => '55712',
                        'PALBAPANG' => '55713',
                        'TRIRENGGO' => '55714',
                        'SABDODADI' => '55715',
                    ],
                    'JETIS' => [
                        'TRIMULYO' => '55781',
                        'CATURHARJO' => '55781',
                        'SUMBERAGUNG' => '55781',
                        'PATALAN' => '55781',
                    ],
                    'IMOGIRI' => [
                        'SELOPAMIORO' => '55782',
                        'SRIHARJO' => '55782',
                        'WUKIRSARI' => '55782',
                        'KEBONAGUNG' => '55782',
                        'KARANGTENGAH' => '55782',
                        'GIRIREJO' => '55782',
                        'KARANGTALUN' => '55782',
                        'IMOGIRI' => '55782',
                    ],
                    'DLINGO' => [
                        'MANGUNAN' => '55783',
                        'MUNTUK' => '55783',
                        'TERONG' => '55783',
                        'TEMUWUH' => '55783',
                        'JATIMULYO' => '55783',
                        'DLINGO' => '55783',
                    ],
                    'PLERET' => [
                        'WONOLELO' => '55791',
                        'BAWURAN' => '55791',
                        'PLERET' => '55791',
                        'WONOKROMO' => '55791',
                        'SEGOROYOSO' => '55791',
                    ],
                    'PIYUNGAN' => [
                        'SITIMULYO' => '55792',
                        'SRIMULYO' => '55792',
                        'SRIMARTANI' => '55792',
                    ],
                    'BANGUNTAPAN' => [
                        'JAGALAN' => '55198',
                        'SINGOSAREN' => '55198',
                        'BANGUNTAPAN' => '55198',
                        'BATURETNO' => '55197',
                        'POTORONO' => '55196',
                        'JAMBIDAN' => '55195',
                        'WIROKERTEN' => '55194',
                        'TAMANAN' => '55191',
                    ],
                    'SEWON' => [
                        'TIMBULHARJO' => '55186',
                        'PENDOWOHARJO' => '55185',
                        'BANGUNHARJO' => '55187',
                        'PANGGUNGHARJO' => '55188',
                    ],
                    'KASIHAN' => [
                        'BANGUNJIWO' => '55184',
                        'TIRTONIRMOLO' => '55181',
                        'TAMANTIRTO' => '55183',
                        'NGESTIHARJO' => '55182',
                    ],
                    'PAJANGAN' => [
                        'SENDANGSARI' => '55751',
                        'GUWOSARI' => '55751',
                        'TRIWIDADI' => '55751',
                    ],
                    'SEDAYU' => [
                        'ARGODADI' => '55752',
                        'ARGOREJO' => '55752',
                        'ARGOSARI' => '55752',
                        'ARGOMULYO' => '55752',
                    ],
                ],
            ],
            'KABUPATEN KULON PROGO' => [
                'code' => '3401',
                'districts' => [
                    'TEMON' => [
                        'TEMON WETAN' => '55654',
                        'TEMON KULON' => '55654',
                        'KALIGINTUNG' => '55654',
                        'PLUMBON' => '55654',
                        'KEDUNDANG' => '55654',
                        'DEMEN' => '55654',
                        'KULUR' => '55654',
                        'KALIDENGEN' => '55654',
                        'PALIHAN' => '55654',
                        'SINDUTAN' => '55654',
                        'JANGKARAN' => '55654',
                        'JANTEN' => '55654',
                        'GLAGAH' => '55654',
                        'KEBONREJO' => '55654',
                        'KARANGWULUH' => '55654',
                    ],
                    'WATES' => [
                        'SOGAN' => '55651',
                        'KULWARU' => '55651',
                        'NGESTIHARJO' => '55651',
                        'BENDUNGAN' => '55651',
                        'TRIHARJO' => '55651',
                        'WATES' => '55651',
                        'GIRIPENI' => '55651',
                        'KARANGWUNI' => '55651',
                    ],
                    'PANJATAN' => [
                        'GARONGAN' => '55655',
                        'PLERET' => '55655',
                        'BUGEL' => '55655',
                        'KANOMAN' => '55655',
                        'DEPOK' => '55655',
                        'BOJONG' => '55655',
                        'TAYUBAN' => '55655',
                        'GOTAKAN' => '55655',
                        'PANJATAN' => '55655',
                        'CERME' => '55655',
                        'KREMBANGAN' => '55655',
                    ],
                    'GALUR' => [
                        'KARANGSEWU' => '55653',
                        'BANARAN' => '55653',
                        'KRANGGAN' => '55653',
                        'BROSOT' => '55653',
                        'PANDOWAN' => '55653',
                        'NOMPOREJO' => '55653',
                        'TIRTORAHAYU' => '55653',
                    ],
                    'LENDAH' => [
                        'JATIREJO' => '55663',
                        'WAHYUHARJO' => '55663',
                        'BUMIREJO' => '55663',
                        'SIDOREJO' => '55663',
                        'GULUREJO' => '55663',
                        'NGENTAKREJO' => '55663',
                    ],
                    'SENTOLO' => [
                        'BANGUNCIPTO' => '55664',
                        'SENTOLO' => '55664',
                        'SALAMREJO' => '55664',
                        'SUKORENO' => '55664',
                        'TUKSONO' => '55664',
                        'SRIKAYANGAN' => '55664',
                        'DEMANGREJO' => '55664',
                        'KALIAGUNG' => '55664',
                    ],
                    'PENGASIH' => [
                        'TAWANGSARI' => '55652',
                        'KARANGSARI' => '55652',
                        'SENDANGSARI' => '55652',
                        'PENGASIH' => '55652',
                        'MARGOSARI' => '55652',
                        'KEDUNGSARI' => '55652',
                        'SIDOMULYO' => '55652',
                    ],
                    'KOKAP' => [
                        'HARGOREJO' => '55661',
                        'HARGOWILIS' => '55661',
                        'HARGOTIRTO' => '55661',
                        'HARGOMULYO' => '55661',
                        'KALIREJO' => '55661',
                    ],
                    'GIRIMULYO' => [
                        'PURWOSARI' => '55674',
                        'PENDOWOREJO' => '55674',
                        'GIRIPURWO' => '55674',
                        'JATIMULYO' => '55674',
                    ],
                    'NANGGULAN' => [
                        'BANYUROTO' => '55671',
                        'DONOMULYO' => '55671',
                        'WIJIMULYO' => '55671',
                        'TANJUNGHARJO' => '55671',
                        'JATISARONO' => '55671',
                        'KEMBANG' => '55671',
                    ],
                    'SAMIGALUH' => [
                        'GERBOSARI' => '55673',
                        'NGARGOSARI' => '55673',
                        'BANJARSARI' => '55673',
                        'KEBONHARJO' => '55673',
                        'PURWOHARJO' => '55673',
                        'BANJARHARJO' => '55673',
                        'PAGERHARJO' => '55673',
                        'SIDOHARJO' => '55673',
                    ],
                    'KALIBAWANG' => [
                        'BANJARARUM' => '55672',
                        'BANJARASRI' => '55672',
                        'BANJARHARJO' => '55672',
                        'BANJAROYO' => '55672',
                    ],
                ],
            ],
            'KABUPATEN GUNUNGKIDUL' => [
                'code' => '3403',
                'districts' => [
                    'WONOSARI' => [
                        'WONOSARI' => '55812',
                        'BALEHARJO' => '55811',
                        'KEPEK' => '55813',
                        'PIYAMAN' => '55812',
                        'GARI' => '55812',
                        'KARANGTENGAH' => '55812',
                        'SELANG' => '55812',
                        'SIRAMAN' => '55812',
                        'WUNUNG' => '55812',
                        'PULUTAN' => '55812',
                        'WARENG' => '55812',
                        'MULO' => '55812',
                        'DUWET' => '55812',
                        'KARANGREJEK' => '55812',
                    ],
                    'NGLIPAR' => [
                        'NGLIPAR' => '55852',
                        'KEDUNGKERIS' => '55852',
                        'PENGKOL' => '55852',
                        'KEDUNGPOH' => '55852',
                        'KATONGAN' => '55852',
                        'PILANGREJO' => '55852',
                        'NATAH' => '55852',
                    ],
                    'PLAYEN' => [
                        'PLAYEN' => '55861',
                        'BANYUSOCO' => '55861',
                        'BLEBERAN' => '55861',
                        'GETAS' => '55861',
                        'DENGOK' => '55861',
                        'NGLERI' => '55861',
                        'NGUNUT' => '55861',
                        'LOGANDENG' => '55861',
                        'GADING' => '55861',
                        'BANARAN' => '55861',
                        'PLEMBUTAN' => '55861',
                        'BUNDER' => '55861',
                        'NGLAWAN' => '55861',
                    ],
                    'PATUK' => [
                        'PATUK' => '55862',
                        'BEJI' => '55862',
                        'PENGKOK' => '55862',
                        'SEMOYO' => '55862',
                        'SALAM' => '55862',
                        'PUTAT' => '55862',
                        'NGORO-ORO' => '55862',
                        'NGLANGGERAN' => '55862',
                        'TERBAH' => '55862',
                        'BUNDER' => '55862',
                        'NGLEGI' => '55862',
                    ],
                    'PALIYAN' => [
                        'PAMPANG' => '55871',
                        'KARANGASEM' => '55871',
                        'KARANGDUWET' => '55871',
                        'SODO' => '55871',
                        'GIRING' => '55871',
                        'MULUSAN' => '55871',
                        'KARANGMOJO' => '55871',
                    ],
                    'PANGGANG' => [
                        'GIRIKARTO' => '55872',
                        'GIRISEKAR' => '55872',
                        'GIRIMULYO' => '55872',
                        'GIRIHARJO' => '55872',
                        'GIRISUKO' => '55872',
                        'GIRIWUNGU' => '55872',
                    ],
                    'SAPTOSARI' => [
                        'JETIS' => '55871',
                        'KANIGORO' => '55871',
                        'KEPEK' => '55871',
                        'KRAMBILSAWIT' => '55871',
                        'MONGGOL' => '55871',
                        'NGLORO' => '55871',
                        'PLANJAN' => '55871',
                    ],
                    'TEPUS' => [
                        'GIRIPANGGUNG' => '55881',
                        'SIDOHARJO' => '55881',
                        'PURWODADI' => '55881',
                        'TEPUS' => '55881',
                        'SUMBERWUNGU' => '55881',
                    ],
                    'TANJUNGSARI' => [
                        'BANJAREJO' => '55881',
                        'HARGOSARI' => '55881',
                        'KEMADANG' => '55881',
                        'KEMIRI' => '55881',
                        'NGESTIREJO' => '55881',
                    ],
                    'RONGKOP' => [
                        'BOTODAYAAN' => '55883',
                        'BOHOL' => '55883',
                        'KARANGWUNI' => '55883',
                        'MELIKAN' => '55883',
                        'PETIR' => '55883',
                        'PRINGOMBO' => '55883',
                        'PUCANGANOM' => '55883',
                        'SEMUGIH' => '55883',
                    ],
                    'GIRISUBO' => [
                        'BALONG' => '55883',
                        'JEPITU' => '55883',
                        'KARANGAWEN' => '55883',
                        'JERUKWUDEL' => '55883',
                        'PUCUNG' => '55883',
                        'SONGBANYU' => '55883',
                        'TILENG' => '55883',
                        'NGLINDUR' => '55883',
                    ],
                    'SEMANU' => [
                        'CANDIREJO' => '55893',
                        'DADAPAYU' => '55893',
                        'NGEPOSARI' => '55893',
                        'PACAREJO' => '55893',
                        'SEMANU' => '55893',
                    ],
                    'PONJONG' => [
                        'BEDOYO' => '55892',
                        'GENJAHAN' => '55892',
                        'GOMBANG' => '55892',
                        'KARANGASEM' => '55892',
                        'KENTENG' => '55892',
                        'PONJONG' => '55892',
                        'SAWAHAN' => '55892',
                        'SIDOREJO' => '55892',
                        'SUMBERGIRI' => '55892',
                        'TAMBAKROMO' => '55892',
                        'UMBULREJO' => '55892',
                    ],
                    'KARANGMOJO' => [
                        'BEJIHARJO' => '55891',
                        'BENDUNGAN' => '55891',
                        'GEDANGREJO' => '55891',
                        'JATIAYU' => '55891',
                        'KARANGMOJO' => '55891',
                        'KELOR' => '55891',
                        'NGAWIS' => '55891',
                        'NGIPAK' => '55891',
                        'WILADEG' => '55891',
                    ],
                    'SEMIN' => [
                        'BENDUNG' => '55854',
                        'BULUREJO' => '55854',
                        'CANDIREJO' => '55854',
                        'KALITEKUK' => '55854',
                        'KARANGSARI' => '55854',
                        'KEMEJING' => '55854',
                        'PUNDUNGSARI' => '55854',
                        'REJOSARI' => '55854',
                        'SEMIN' => '55854',
                        'SUMBEREJO' => '55854',
                    ],
                    'NGAWEN' => [
                        'BEJI' => '55853',
                        'JURANGJERO' => '55853',
                        'KAMPUNG' => '55853',
                        'SAMBIREJO' => '55853',
                        'TANCEP' => '55853',
                        'WATUSIGAR' => '55853',
                    ],
                    'GEDANGSARI' => [
                        'HARGOMULYO' => '55863',
                        'MERTELU' => '55863',
                        'NGALANG' => '55863',
                        'SERUT' => '55863',
                        'TEGALREJO' => '55863',
                        'WATUGAJAH' => '55863',
                        'SAMPANG' => '55863',
                    ],
                    'PURWOSARI' => [
                        'GIRIASIH' => '55873',
                        'GIRICAHYO' => '55873',
                        'GIRIJATI' => '55873',
                        'GIRIPURWO' => '55873',
                        'GIRITIRTO' => '55873',
                    ],
                ],
            ],
        ];
    }
}
