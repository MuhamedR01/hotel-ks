import React from "react";

const TermsOfService = () => {
  return (
    <div className="min-h-screen bg-gray-50 py-12">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8">
        <div className="bg-white rounded-lg shadow p-8 max-w-3xl mx-auto">
          <h1 className="text-2xl font-bold mb-4">Kushtet e Shërbimit</h1>

          <p className="text-gray-700 mb-4">
            Mirë se vini në <strong>minimodaks</strong>. Duke hyrë dhe duke
            përdorur këtë faqe, ju pranoni kushtet e mëposhtme. Nëse nuk
            pajtoheni me këto kushte, ju lutemi mos e përdorni këtë shërbim.
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">
            1. Pranimi i Kushteve
          </h2>
          <p className="text-gray-700 mb-4">
            Përdorimi i kësaj faqeje përbën pranimin e plotë të këtyre kushteve
            të shërbimit dhe të politikës sonë të privatësisë. Ne rezervojmë të
            drejtën të modifikojmë këto kushte në çdo kohë, dhe ndryshimet hyjnë
            në fuqi menjëherë pas publikimit në këtë faqe.
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">
            2. Produktet dhe Qmimet
          </h2>
          <p className="text-gray-700 mb-4">
            Bëjmë çmos që informacioni për produktet, çmimet dhe
            disponueshmërinë të jetë i saktë. Megjithatë, mund të ndodhin gabime
            teknike ose ndryshime pa paralajmërim. Rezervojmë të drejtën të
            korrigjojmë gabimet dhe të anulojmë porositë e bëra me çmime të
            pasakta.
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">3. Porositë</h2>
          <p className="text-gray-700 mb-4">
            Çdo porosi që bëhet në faqen tonë konsiderohet një ofertë për blerje
            të produkteve. Ne rezervojmë të drejtën të pranojmë ose të refuzojmë
            porositë sipas gjykimit tonë. Pas konfirmimit të porosisë, ju do të
            merrni një njoftim me detajet.
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">4. Dërgesa</h2>
          <ul className="list-disc list-inside text-gray-700 mb-4 space-y-1">
            <li>
              <strong>Kosovë:</strong> 1–3 ditë pune nga momenti i konfirmimit
              të porosisë.
            </li>
            <li>
              <strong>Shqipëri dhe Maqedoni e Veriut:</strong> 2–5 ditë pune.
            </li>
            <li>
              Afatet mund të ndryshojnë për shkak të festave zyrtare ose
              rrethanave të jashtëzakonshme.
            </li>
          </ul>

          <h2 className="text-lg font-semibold mt-6 mb-2">
            5. Kthimet dhe Ndërrimet
          </h2>
          <p className="text-gray-700 mb-4">
            Produktet mund të ndërrohen ose kthehen brenda afatit të
            parashikuar, me kusht që të jenë në gjendje origjinale, të
            papërdorura dhe me etiketat e paprekura.{" "}
            <strong>Produktet e brendshme nuk ndërrohen dhe as kthehen</strong>{" "}
            për arsye higjienike dhe sigurie.
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">6. Pagesa</h2>
          <p className="text-gray-700 mb-4">
            Pagesa bëhet me para në dorë gjatë dorëzimit (cash on delivery),
            përveç rasteve kur specifikohet ndryshe. Klienti është përgjegjës
            për konfirmimin e shumës përpara dorëzimit.
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">
            7. Pronësia Intelektuale
          </h2>
          <p className="text-gray-700 mb-4">
            I gjithë përmbajtja e kësaj faqeje (tekstet, fotografitë, logot,
            dizajni) është pronë e minimodaks dhe e mbrojtur nga ligjet e të
            drejtës së autorit. Ndalohet kopjimi, shpërndarja ose përdorimi i
            këtyre materialeve pa lejen paraprake me shkrim.
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">
            8. Llogaria e Përdoruesit
          </h2>
          <p className="text-gray-700 mb-4">
            Ju jeni përgjegjës për ruajtjen e konfidencialitetit të
            kredencialeve tuaja dhe për të gjitha veprimet që ndodhin nën
            llogarinë tuaj. Ju duhet të na njoftoni menjëherë për çdo përdorim
            të paautorizuar.
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">
            9. Kufizimi i Përgjegjësisë
          </h2>
          <p className="text-gray-700 mb-4">
            minimodaks nuk mban përgjegjësi për dëmet indirekte, të rastësishme
            ose pasuese që rrjedhin nga përdorimi i kësaj faqeje ose i
            produkteve të blera, përtej vlerës së produktit të blerë.
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">
            10. Ligji i Aplikueshëm
          </h2>
          <p className="text-gray-700 mb-4">
            Këto kushte rregullohen nga ligjet e Republikës së Kosovës. Çdo
            mosmarrëveshje do të zgjidhet në gjykatat kompetente të Kosovës.
          </p>

          <h2 className="text-lg font-semibold mt-6 mb-2">11. Kontakt</h2>
          <p className="text-gray-700 mb-2">
            Për pyetje në lidhje me këto kushte, na kontaktoni:
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

          <p className="text-sm text-gray-500 mt-6">
            Dokument i fundit i përditësuar: Prill 2026
          </p>
        </div>
      </div>
    </div>
  );
};

export default TermsOfService;
