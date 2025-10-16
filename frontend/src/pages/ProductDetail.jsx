
import { useState, useEffect } from 'react'
import { useParams, Link, useNavigate } from 'react-router-dom'
import { useCart } from '../context/CartContext'

const ProductDetail = () => {
  const { id } = useParams()
  const navigate = useNavigate()
  const { addToCart } = useCart()
  const [product, setProduct] = useState(null)
  const [loading, setLoading] = useState(true)
  const [quantity, setQuantity] = useState(1)
  const [selectedImage, setSelectedImage] = useState(0)
  const [relatedProducts, setRelatedProducts] = useState([])

  useEffect(() => {
    // Mock product data - replace with API call later
    setTimeout(() => {
      const mockProducts = [
        {
          id: 1,
          name: "Çarçafë Premium Pambuku",
          description: "Çarçafë luksoze 100% pambuk egjiptian me 800 fije. Të disponueshme në madhësi Queen dhe King.",
          fullDescription: "Përjetoni komoditetin maksimal me Çarçafët tona Premium të Pambukut. Të bëra nga 100% pambuk egjiptian me 800 fije, këto çarçafë ofrojnë butësi dhe qëndrueshmëri të pakrahasueshme. Materiali i frymëzueshëm siguron një gjumë të rehatshëm në çdo stinë. Të disponueshme në madhësi Queen dhe King, me një shumëllojshmëri ngjyrash elegante për t'u përshtatur me çdo dhomë gjumi. Të larueshme në makinë dhe të dizajnuara për të zgjatur vite.",
          price: 89.99,
          category: "shtretër",
          images: [
            "https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800",
            "https://images.unsplash.com/photo-1631049035182-249067d7618e?w=800",
            "https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=800"
          ],
          rating: 4.8,
          reviews: 124,
          inStock: true,
          features: [
            "800 fije pambuk egjiptian",
            "Material i butë dhe i frymëzueshëm",
            "I larëshëm në makinë",
            "Të disponueshme në ngjyra të shumta",
            "Madhësi Queen dhe King"
          ],
          specifications: {
            "Materiali": "100% Pambuk Egjiptian",
            "Numri i Fijeve": "800",
            "Madhësitë": "Queen, King",
            "Kujdesi": "Lani në ujë të ftohtë, tharje në temperaturë të ulët",
            "Origjina": "Egjipt"
          }
        },
        {
          id: 2,
          name: "Set Peshqirësh Cilësi Hoteli",
          description: "Set peshqirësh me cilësi profesionale që përfshin peshqirë banje, peshqirë dore dhe peshqirë fytyre. Ultra-thithës dhe të qëndrueshëm.",
          fullDescription: "Ngrini përvojën tuaj të banjës me Setin tonë të Peshqirëve me Cilësi Hoteli. Ky set gjithëpërfshirës përfshin 2 peshqirë banje, 2 peshqirë dore dhe 2 peshqirë fytyre, të gjitha të punuara nga pambuku premium turk. Materiali ultra-thithës ju thate shpejt duke mbetur i butë kundër lëkurës suaj. Këto peshqirë janë dizajnuar për t'i rezistuar larjeve të shpeshta duke ruajtur ndjenjën e tyre të butë dhe ngjyrën e gjallë.",
          price: 49.99,
          category: "banjo",
          images: [
            "https://images.unsplash.com/photo-1600334129128-685c5582fd35?w=800",
            "https://images.unsplash.com/photo-1582735689369-4fe89db7114c?w=800",
            "https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=800"
          ],
          rating: 4.6,
          reviews: 89,
          inStock: true,
          features: [
            "Set 6 copë peshqirësh",
            "Konstruksion pambuk turk",
            "Ultra-thithës",
            "Ngjyra rezistente ndaj zbardhjes",
            "Cilësi e nivelit të hotelit"
          ],
          specifications: {
            "Materiali": "100% Pambuk Turk",
            "Seti Përfshin": "2 Peshqirë Banje, 2 Peshqirë Dore, 2 Peshqirë Fytyre",
            "Pesha": "600 GSM",
            "Kujdesi": "Lani në ujë të ngrohtë",
            "Origjina": "Turqi"
          }
        },
        {
          id: 3,
          name: "Jastëk Memory Foam",
          description: "Jastëk ergonomik memory foam me teknologji xhel ftohës. Perfekt për një gjumë të qetë natën.",
          fullDescription: "Zbuloni gjumin e përsosur me Jastëkun tonë Memory Foam. Me teknologji të avancuar xhel ftohës, ky jastëk ju mban të rehatshëm gjatë gjithë natës. Dizajni ergonomik ofron mbështetje optimale për qafën dhe kokën, duke reduktuar pikat e presionit dhe duke promovuar rreshtimin e duhur të shtyllës kurrizore. Mbulesa e frymëzueshme është e heqshme dhe e larëshme në makinë për kujdes të lehtë.",
          price: 39.99,
          category: "shtretër",
          images: [
            "https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?w=800",
            "https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=800",
            "https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800"
          ],
          rating: 4.9,
          reviews: 203,
          inStock: true,
          features: [
            "Teknologji xhel ftohës",
            "Dizajn ergonomik",
            "Lehtësim i pikave të presionit",
            "Mbulese e heqshme e larëshme",
            "Materiale hipoalergjike"
          ],
          specifications: {
            "Materiali": "Memory Foam me Xhel Ftohës",
            "Madhësia": "Standard (20 x 26)",
            "Mbulesa": "Material i derivuar nga bambuja",
            "Kujdesi": "Pastroni me njollë foamin, lani mbulesën në makinë",
            "Garancia": "5 vjet"
          }
        }
      ]

      const foundProduct = mockProducts.find(p => p.id === parseInt(id))
      setProduct(foundProduct || mockProducts[0])
      
      // Set related products (excluding current product)
      setRelatedProducts(mockProducts.filter(p => p.id !== parseInt(id)).slice(0, 4))
      
      setLoading(false)
    }, 500)
  }, [id])

  const handleAddToCart = () => {
    if (!product) return;
    
    for (let i = 0; i < quantity; i++) {
      addToCart(product)
    }
    // Show toast notification
    const toast = document.createElement('div')
    toast.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50'
    toast.textContent = `${quantity} ${product.name} u shtua në shportë!`
    document.body.appendChild(toast)
    setTimeout(() => toast.remove(), 3000)
  }

  const handleBuyNow = () => {
    if (!product) return;
    handleAddToCart()
    navigate('/cart')
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-xl text-gray-600">Duke ngarkuar produktin...</p>
        </div>
      </div>
    )
  }

  if (!product) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <h2 className="text-2xl font-bold text-gray-900 mb-4">Produkti nuk u gjet</h2>
          <Link to="/products" className="text-blue-600 hover:text-blue-700">
            Kthehu te Produktet
          </Link>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gray-50 py-12">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8">
        {/* Breadcrumb */}
        <nav className="mb-8 text-sm">
          <ol className="flex items-center space-x-2 text-gray-600">
            <li><Link to="/" className="hover:text-blue-600">Ballina</Link></li>
            <li>/</li>
            <li><Link to="/products" className="hover:text-blue-600">Produktet</Link></li>
            <li>/</li>
            <li className="text-gray-900 font-semibold">{product.name}</li>
          </ol>
        </nav>

        {/* Product Details */}
        <div className="bg-white rounded-lg shadow-lg overflow-hidden mb-12">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 p-8">
            {/* Images */}
            <div>
              <div className="mb-4 rounded-lg overflow-hidden bg-gray-100">
                <img
                  src={product.images[selectedImage]}
                  alt={product.name}
                  className="w-full h-96 object-cover"
                />
              </div>
              <div className="grid grid-cols-3 gap-4">
                {product.images.map((image, index) => (
                  <button
                    key={index}
                    onClick={() => setSelectedImage(index)}
                    className={`rounded-lg overflow-hidden border-2 transition-all ${
                      selectedImage === index ? 'border-blue-600' : 'border-gray-200 hover:border-gray-300'
                    }`}
                  >
                    <img src={image} alt={`${product.name} ${index + 1}`} className="w-full h-24 object-cover" />
                  </button>
                ))}
              </div>
            </div>

            {/* Product Info */}
            <div>
              <div className="mb-4">
                <span className="inline-block px-3 py-1 bg-blue-100 text-blue-600 text-sm font-semibold rounded-full uppercase">
                  {product.category}
                </span>
              </div>
              
              <h1 className="text-4xl font-bold text-gray-900 mb-4">{product.name}</h1>
              
              <div className="flex items-center mb-6">
                <div className="flex items-center">
                  <span className="text-yellow-400 text-2xl">★</span>
                  <span className="ml-2 text-xl font-semibold text-gray-900">{product.rating}</span>
                  <span className="ml-2 text-gray-600">({product.reviews} reviews)</span>
                </div>
              </div>

              <div className="mb-6">
                <span className="text-4xl font-bold text-blue-600">${product.price}</span>
                {product.inStock ? (
                  <span className="ml-4 text-green-600 font-semibold">Në Magazinë</span>
                ) : (
                  <span className="ml-4 text-red-600 font-semibold">Nuk ka në magazinë</span>
                )}
              </div>

              <p className="text-gray-700 text-lg mb-6 leading-relaxed">{product.fullDescription}</p>

              {/* Quantity Selector */}
              <div className="mb-6">
                <label className="block text-sm font-semibold text-gray-900 mb-2">Sasia</label>
                <div className="flex items-center space-x-4">
                  <button
                    onClick={() => setQuantity(Math.max(1, quantity - 1))}
                    className="w-10 h-10 rounded-lg border-2 border-gray-300 hover:border-blue-600 flex items-center justify-center font-semibold"
                  >
                    -
                  </button>
                  <span className="text-xl font-semibold w-12 text-center">{quantity}</span>
                  <button
                    onClick={() => setQuantity(quantity + 1)}
                    className="w-10 h-10 rounded-lg border-2 border-gray-300 hover:border-blue-600 flex items-center justify-center font-semibold"
                  >
                    +
                  </button>
                </div>
              </div>

              {/* Action Buttons */}
              <div className="flex gap-4 mb-8">
                <button
                  onClick={handleAddToCart}
                  className="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300"
                >
                  Shto në Shportë
                </button>
                <button
                  onClick={handleBuyNow}
                  className="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300"
                >
                  Bli Tani
                </button>
              </div>

              {/* Features */}
              <div className="mb-8">
                <h3 className="text-xl font-bold text-gray-900 mb-4">Veçoritë Kryesore</h3>
                <ul className="space-y-2">
                  {product.features.map((feature, index) => (
                    <li key={index} className="flex items-start">
                      <span className="text-green-500 mr-2">✓</span>
                      <span className="text-gray-700">{feature}</span>
                    </li>
                  ))}
                </ul>
              </div>

              {/* Specifications */}
              <div>
                <h3 className="text-xl font-bold text-gray-900 mb-4">Specifikimet</h3>
                <div className="grid grid-cols-2 gap-4">
                  {Object.entries(product.specifications).map(([key, value]) => (
                    <div key={key} className="border-b border-gray-200 pb-2">
                      <span className="font-semibold text-gray-900">{key}:</span>
                      <span className="ml-2 text-gray-700">{value}</span>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Related Products */}
        <div>
          <h2 className="text-3xl font-bold text-gray-900 mb-8">Produkte të Ligjera</h2>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {relatedProducts.map((relatedProduct) => (
              <div key={relatedProduct.id} className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <img 
                  src={relatedProduct.images[0]} 
                  alt={relatedProduct.name} 
                  className="w-full h-48 object-cover"
                />
                <div className="p-4">
                  <h3 className="font-bold text-lg text-gray-900 mb-2">{relatedProduct.name}</h3>
                  <p className="text-gray-700 mb-2">${relatedProduct.price}</p>
                  <Link 
                    to={`/products/${relatedProduct.id}`}
                    className="text-blue-600 hover:text-blue-800 font-semibold"
                  >
                    Shiko Detajet
                  </Link>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  )
}

export default ProductDetail
