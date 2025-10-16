
import { Link } from 'react-router-dom'

const About = () => {
  return (
    <div className="min-h-screen bg-gray-50">
      {/* Hero Section */}
      <section className="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-20">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h1 className="text-4xl md:text-5xl font-bold mb-4">Rreth Hotel KS</h1>
          <p className="text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto">
            Duke sjellë përvojën e luksit të hotelit në shtëpinë tuaj që nga viti 2020
          </p>
        </div>
      </section>

      {/* Our Story */}
      <section className="py-16">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="max-w-4xl mx-auto">
            <div className="bg-white rounded-lg shadow-md p-8 md:p-12">
              <h2 className="text-3xl font-bold text-gray-900 mb-6">Historia Jonë</h2>
              <div className="prose prose-lg text-gray-600 space-y-4">
                <p>
                  Hotel KS u themelua me një mision të thjeshtë: të bëjë produkte me cilësi premium hoteli të aksesueshme për të gjithë. 
                  Ne besojmë se komfori dhe luksi që përjetoni në një hotel pesë yje nuk duhet të kufizohet vetëm në udhëtimet tuaja.
                </p>
                <p>
                  Udhëtimi ynë filloi kur themeluesit tanë, pas vitesh në industrinë e mikpritjes, vunë re një boshllëk në treg. 
                  Mysafirët shpesh pyesnin se ku mund të blenin çarçafët e rehatshëm, peshqirët e butë dhe pajisjet me cilësi 
                  që përjetonin gjatë qëndrimit të tyre. Atëherë lindi ideja për Hotel KS.
                </p>
                <p>
                  Sot, ne bashkëpunojmë drejtpërdrejt me të njëjtët furnizues që ofrojnë produkte për hotelet luksoze në të gjithë botën. 
                  Kjo na lejon të ju ofrojmë të njëjtën cilësi premium me çmime konkurruese, duke sjellë përvojën e hotelit 
                  drejt në derën tuaj.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Our Values */}
      <section className="py-16 bg-white">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-3xl font-bold text-gray-900 mb-4">Vlerat Tona</h2>
            <p className="text-gray-600 max-w-2xl mx-auto">
              Këto parime kryesore udhëheqin gjithçka që bëjmë në Hotel KS
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <div className="text-center p-6">
              <div className="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                <svg className="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-gray-900 mb-2">Cilësia së Pari</h3>
              <p className="text-gray-600">
                Ne kurrë nuk kompromitojmë në cilësi. Çdo produkt plotëson të njëjtat standarde si hotelet luksoze.
              </p>
            </div>

            <div className="text-center p-6">
              <div className="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                <svg className="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-gray-900 mb-2">Çmime të Drejta</h3>
              <p className="text-gray-600">
                Cilësia premium nuk duhet të nënkuptojë çmime premium. Ne i mbajmë kostot tona transparente dhe të drejta.
              </p>
            </div>

            <div className="text-center p-6">
              <div className="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                <svg className="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-gray-900 mb-2">Kënaqësia e Klientit</h3>
              <p className="text-gray-600">
                Lumturia juaj është suksesi ynë. Ne jemi këtu për të siguruar që të doni çdo blerje.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* Stats Section */}
      <section className="py-16 bg-blue-600 text-white">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
              <div className="text-4xl md:text-5xl font-bold mb-2">10K+</div>
              <div className="text-blue-100">Klientë të Lumtur</div>
            </div>
            <div>
              <div className="text-4xl md:text-5xl font-bold mb-2">500+</div>
              <div className="text-blue-100">Produkte</div>
            </div>
            <div>
              <div className="text-4xl md:text-5xl font-bold mb-2">50+</div>
              <div className="text-blue-100">Partnerë Hotelesh</div>
            </div>
            <div>
              <div className="text-4xl md:text-5xl font-bold mb-2">4.8</div>
              <div className="text-blue-100">Vlerësim Mesatar</div>
            </div>
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-16">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-12 text-center text-white">
            <h2 className="text-3xl md:text-4xl font-bold mb-4">
              Gati të Përjetoni Luksin e Hotelit?
            </h2>
            <p className="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
              Shfletoni koleksionin tonë dhe sillni komfortin e një hoteli pesë yje në shtëpinë tuaj sot.
            </p>
            <Link
              to="/products"
              className="inline-block bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors duration-200"
            >
              Blej Tani
            </Link>
          </div>
        </div>
      </section>
    </div>
  )
}

export default About
