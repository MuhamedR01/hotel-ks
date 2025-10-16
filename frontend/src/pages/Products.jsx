
import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { useCart } from '../context/CartContext'

const Products = () => {
  const { addToCart } = useCart()
  const [products, setProducts] = useState([])
  const [loading, setLoading] = useState(true)
  const [selectedCategory, setSelectedCategory] = useState('të gjitha')
  const [sortBy, setSortBy] = useState('default')
  const [searchQuery, setSearchQuery] = useState('')

  const categories = ['të gjitha', 'shtretër', 'banjo', 'dhoma', 'aksesorë']

  useEffect(() => {
    // Mock products data - replace with API call later
    setTimeout(() => {
      const mockProducts = [
        {
          id: 1,
          name: "Çarçafë Premium Pambuku",
          description: "Çarçafë luksoze 100% pambuk egjiptian me 800 fije. Të disponueshme në madhësi Queen dhe King.",
          price: 89.99,
          category: "shtretër",
          image: "https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=400",
          rating: 4.8,
          reviews: 124,
          inStock: true
        },
        {
          id: 2,
          name: "Set Peshqirësh Cilësi Hoteli",
          description: "Set peshqirësh me cilësi profesionale që përfshin peshqirë banje, peshqirë dore dhe peshqirë fytyre.",
          price: 49.99,
          category: "banjo",
          image: "https://images.unsplash.com/photo-1600334129128-685c5582fd35?w=400",
          rating: 4.6,
          reviews: 89,
          inStock: true
        },
        {
          id: 3,
          name: "Jastëk Memory Foam",
          description: "Jastëk ergonomik memory foam me teknologji xhel ftohës. Perfekt për një gjumë të qetë natën.",
          price: 39.99,
          category: "shtretër",
          image: "https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?w=400",
          rating: 4.9,
          reviews: 203,
          inStock: true
        },
        {
          id: 4,
          name: "Peshqir Banjoje Luksoz",
          description: "Peshqir banjoje i butë me material pambuku turk. Një madhësi për të gjithë.",
          price: 69.99,
          category: "banjo",
          image: "https://images.unsplash.com/photo-1582735689369-4fe89db7114c?w=400",
          rating: 4.7,
          reviews: 156,
          inStock: true
        },
        {
          id: 5,
          name: "Perde Errësimi",
          description: "Perde të rënda për errësim të plotë dhe privatësi. Të disponueshme në ngjyra të shumta.",
          price: 79.99,
          category: "dhoma",
          image: "https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=400",
          rating: 4.5,
          reviews: 98,
          inStock: true
        },
        {
          id: 6,
          name: "Pantofla Hoteli",
          description: "Pantofla të rehatshme me nënshtresë anti-rrëshqitëse. Paketë me 10 palë.",
          price: 24.99,
          category: "aksesorë",
          image: "https://images.unsplash.com/photo-1603487742131-4160ec999306?w=400",
          rating: 4.4,
          reviews: 67,
          inStock: true
        },
        {
          id: 7,
          name: "Jorgan Komfort",
          description: "Jorgan për të gjitha stinët me mbushje alternative. I larëshëm në makinë.",
          price: 99.99,
          category: "shtretër",
          image: "https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=400",
          rating: 4.8,
          reviews: 145,
          inStock: true
        },
        {
          id: 8,
          name: "Mbrojtës Dysheku",
          description: "Mbrojtës dysheku i papërshkueshëm nga uji dhe i frymëzueshëm. Mbron nga derdhjat dhe alergjenet.",
          price: 34.99,
          category: "shtretër",
          image: "https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=400",
          rating: 4.6,
          reviews: 112,
          inStock: true
        },
        {
          id: 9,
          name: "Set Peshqirësh Fytyre",
          description: "Set 6 peshqirësh fytyre me pambuk premium. Të buta dhe thithëse.",
          price: 19.99,
          category: "banjo",
          image: "https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=400",
          rating: 4.5,
          reviews: 78,
          inStock: true
        },
        {
          id: 10,
          name: "Mbulese Jastëku Mëndafshi",
          description: "Mbulese jastëku prej mëndafshi për flokë dhe lëkurë më të shëndetshme.",
          price: 29.99,
          category: "shtretër",
          image: "https://images.unsplash.com/photo-1631049035182-249067d7618e?w=400",
          rating: 4.7,
          reviews: 134,
          inStock: true
        },
        {
          id: 11,
          name: "Organizues Banjoje",
          description: "Organizues elegant për produkte banjoje. Material inoks.",
          price: 44.99,
          category: "aksesorë",
          image: "https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=400",
          rating: 4.6,
          reviews: 91,
          inStock: true
        },
        {
          id: 12,
          name: "Qilim Dhome Gjumi",
          description: "Qilim i butë dhe i ngrohtë për dhomën e gjumit. Madhësi të ndryshme.",
          price: 59.99,
          category: "dhoma",
          image: "https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=400",
          rating: 4.5,
          reviews: 103,
          inStock: true
        }
      ]
      setProducts(mockProducts)
      setLoading(false)
    }, 500)
  }, [])

  const handleAddToCart = (product) => {
    addToCart(product)
    // Show toast notification
    const toast = document.createElement('div')
    toast.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50'
    toast.textContent = `${product.name} u shtua në shportë!`
    document.body.appendChild(toast)
    setTimeout(() => toast.remove(), 3000)
  }

  // Filter products
  const filteredProducts = products.filter(product => {
    const matchesCategory = selectedCategory === 'të gjitha' || product.category === selectedCategory
    const matchesSearch = product.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                         product.description.toLowerCase().includes(searchQuery.toLowerCase())
    return matchesCategory && matchesSearch
  })

  // Sort products
  const sortedProducts = [...filteredProducts].sort((a, b) => {
    switch (sortBy) {
      case 'price-low':
        return a.price - b.price
      case 'price-high':
        return b.price - a.price
      case 'rating':
        return b.rating - a.rating
      case 'name':
        return a.name.localeCompare(b.name)
      default:
        return 0
    }
  })

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-xl text-gray-600">Duke ngarkuar produktet...</p>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gray-50 py-12">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-4xl font-bold text-gray-900 mb-4">Produktet Tona</h1>
          <p className="text-xl text-gray-600">Zbuloni koleksionin tonë të produkteve me cilësi hoteli</p>
        </div>

        {/* Search and Filters */}
        <div className="bg-white rounded-lg shadow-md p-6 mb-8">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {/* Search */}
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-2">Kërko</label>
              <input
                type="text"
                placeholder="Kërko produkte..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            {/* Category Filter */}
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-2">Kategoria</label>
              <select
                value={selectedCategory}
                onChange={(e) => setSelectedCategory(e.target.value)}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent capitalize"
              >
                {categories.map(category => (
                  <option key={category} value={category} className="capitalize">
                    {category}
                  </option>
                ))}
              </select>
            </div>

            {/* Sort */}
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-2">Rendit sipas</label>
              <select
                value={sortBy}
                onChange={(e) => setSortBy(e.target.value)}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="default">Të parazgjedhura</option>
                <option value="price-low">Çmimi: nga i ulët në të lartë</option>
                <option value="price-high">Çmimi: nga i lartë në të ulët</option>
                <option value="rating">Ratingu më i lartë</option>
                <option value="name">Emri A-Z</option>
              </select>
            </div>
          </div>
        </div>

        {/* Results Count */}
        <div className="mb-6">
          <p className="text-gray-600">
            Duke shfaqur <span className="font-semibold">{sortedProducts.length}</span> produkte
          </p>
        </div>

        {/* Products Grid */}
        {sortedProducts.length > 0 ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {sortedProducts.map((product) => (
              <Link
                key={product.id}
                to={`/product/${product.id}`}
                className="group bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all transform hover:-translate-y-1"
              >
                <div className="relative h-64 overflow-hidden bg-gray-100">
                  <img
                    src={product.image}
                    alt={product.name}
                    className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                  />
                  <div className="absolute top-3 right-3 bg-white px-2 py-1 rounded-full text-sm font-semibold text-gray-700 shadow-md">
                    ⭐ {product.rating}
                  </div>
                </div>
                <div className="p-5">
                  <div className="mb-2">
                    <span className="text-xs font-semibold text-blue-600 uppercase tracking-wide">
                      {product.category}
                    </span>
                  </div>
                  <h3 className="text-lg font-semibold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                    {product.name}
                  </h3>
                  <p className="text-gray-600 text-sm mb-4 line-clamp-2">
                    {product.description}
                  </p>
                  <div className="flex items-center justify-between mb-3">
                    <span className="text-2xl font-bold text-blue-600">${product.price}</span>
                    <span className="text-sm text-gray-500">
                      {product.reviews} recensione
                    </span>
                  </div>
                  <button
                    onClick={(e) => {
                      e.preventDefault()
                      handleAddToCart(product)
                    }}
                    className="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition-colors font-semibold"
                  >
                    Shto në Shportë
                  </button>
                </div>
              </Link>
            ))}
          </div>
        ) : (
          <div className="text-center py-16">
            <svg
              className="w-24 h-24 text-gray-400 mx-auto mb-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            <h3 className="text-2xl font-semibold text-gray-900 mb-2">Nuk u gjet asnjë produkt</h3>
            <p className="text-gray-600 mb-6">Provo të rregullosh kërkimin ose kriteret e filtrimit</p>
            <button
              onClick={() => {
                setSelectedCategory('të gjitha')
                setSearchQuery('')
                setSortBy('default')
              }}
              className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold"
            >
              Pastroni Filtrat
            </button>
          </div>
        )}
      </div>
    </div>
  )
}

export default Products
