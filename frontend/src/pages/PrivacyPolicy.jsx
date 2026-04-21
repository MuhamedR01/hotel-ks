import React from "react";

const PrivacyPolicy = () => {
  return (
    <div className="min-h-screen bg-gray-50 py-12">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8">
        <div className="bg-white rounded-lg shadow p-8 max-w-3xl mx-auto">
          <h1 className="text-2xl font-bold mb-4">Politika e Privatësisë</h1>

          <p className="text-gray-700 mb-4">
            Ky dokument përshkruan se si <strong>minimodaks</strong> mbledh,
            përdor dhe ruan të dhënat tuaja personale. Ne jemi të përkushtuar
            për të respektuar privatësinë tuaj dhe për të siguruar trajtimin e
            sigurt të informacionit tuaj sipas legjislacionit në fuqi.
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">1. Përmbledhje</h2>
          <p className="text-gray-700 mb-4">
            Kjo politikë shpjegon çfarë lloj të dhënash mbledhim (p.sh. emër,
            telefon, adresë), përse i mbledhim, si i përdorim, me kë i ndajmë
            dhe të drejtat që keni mbi këto të dhëna.
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">
            2. Të dhënat që mbledhim
          </h2>
          <ul className="list-disc list-inside text-gray-700 mb-4 space-y-1">
            <li>
              Informacion personal: emër, mbiemër, numër telefoni, adresë.
            </li>
            <li>
              Detajet e porosisë: produktet, sasia, çmimi, madhësitë e
              zgjedhura.
            </li>
            <li>
              Informacione teknike: adresa IP, informacion për shfletuesin dhe
              pajisjen (për qëllime sigurie dhe diagnostikimi).
            </li>
          </ul>

          <h2 className="text-lg font-semibold mt-6 mb-2">
            3. Përse i përdorim të dhënat
          </h2>
          <p className="text-gray-700 mb-4">
            Përdorimi kryesor i të dhënave është për përpunimin dhe dorëzimin e
            porosive, komunikime lidhur me porositë, mbështetje klienti dhe
            përmirësim shërbimesh. Gjithashtu përdorim të dhënat për detyrimet
            ligjore dhe sigurimin e sigurisë në platformë.
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">
            4. Legaliteti i përpunimit
          </h2>
          <p className="text-gray-700 mb-4">
            Përpunimi i të dhënave bazohet në nevojën për të përmbushur
            kontratën (përpunimi i porosisë), në interesin legjitim të sigurisë
            dhe funksionimit të shërbimit, dhe kur është e nevojshme, në
            pëlqimin tuaj për komunikime marketingu.
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">
            5. Si i ruajmë dhe i mbrojmë të dhënat
          </h2>
          <p className="text-gray-700 mb-4">
            Ne ruajmë të dhënat në bazat tona të të dhënave me qasje të kufizuar
            dhe masave të duhura teknike dhe organizative për të parandaluar
            aksesin e paautorizuar. Praktikat tona përfshijnë enkriptim të
            komunikimit, backup-e të rregullta dhe kontroll të qasjes.
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">
            6. Afati i ruajtjes
          </h2>
          <p className="text-gray-700 mb-4">
            Ruajmë të dhënat për kohën e nevojshme për qëllimet e përshkruara,
            ose sipas kërkesave ligjore. Për shembull, të dhënat që lidhen me
            transaksionet financiare ruhen sipas afateve të arkivimit të
            kërkuara nga ligji.
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">
            7. Palët e treta dhe transferimet
          </h2>
          <p className="text-gray-700 mb-4">
            Për të përmbushur porositë, mund të ndajmë informacionin tuaj me
            partnerë të besueshëm të logjistikës, pagesave dhe ofrues të
            shërbimeve të mbështetjes. Ne zgjedhim partnerë që respektojnë
            standardet e nevojshme të sigurisë dhe privatësisë.
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">
            8. Cookies dhe teknologji të ngjashme
          </h2>
          <p className="text-gray-700 mb-4">
            Faqja jonë mund të përdorë cookies ose teknologji të ngjashme për të
            përmirësuar përvojën e përdoruesit dhe për analiza të brendshme.
            Nëse kërkoni, mund të ofrojmë më shumë detaje për cookie-t aktive
            dhe mënyrën e menaxhimit të tyre.
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">
            9. Të drejtat tuaja
          </h2>
          <p className="text-gray-700 mb-4">
            Keni të drejtë të kërkoni qasje, korrigjim, fshirje, kufizim të
            përpunimit, të kundërshtoni përpunimin dhe të kërkoni transferimin e
            të dhënave. Për kërkesa, kontaktoni adresën më poshtë. Në rast
            mosmarrëveshjeje, mund të drejtoheni tek autoriteti kombëtar për
            mbrojtjen e të dhënave.
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">10. Kontakt</h2>
          <p className="text-gray-700 mb-2">
            Për pyetje ose kërkesa në lidhje me privatësinë, ju lutemi
            kontaktoni:
          </p>
          <p className="text-gray-700 font-medium">
            Email:{" "}
            <a
              href="mailto:info@minimodaks.com"
              className="underline hover:text-gray-900"
            >
              info@minimodaks.com
            </a>
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">
            11. Ndryshimet në politikë
          </h2>
          <p className="text-gray-700 mb-4">
            Rezervojmë të drejtën për të përditësuar këtë politikë periodikisht.
            Ndryshimet e rëndësishme do të njoftohen në këtë faqe dhe, kur jetë
            e nevojshme, do t'ju njoftojmë drejtpërdrejt.
          </p>

          <p className="text-sm text-gray-500 mt-6">
            Dokument i fundit i përditësuar: Dhjetor 2025
          </p>
        </div>
      </div>
    </div>
  );
};

export default PrivacyPolicy;
