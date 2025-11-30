
import { useParams, Link, useNavigate } from 'react-router-dom'
import { ChevronLeft, ChevronRight } from 'lucide-react'
import { useCart } from '../context/CartContext'
import { useState, useEffect } from 'react'

const ProductDetail = () => {
  const { id } = useParams()
  const { addToCart } = useCart()
  const navigate = useNavigate()

  const [product, setProduct] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [quantity, setQuantity] = useState(1)
  const [selectedImage, setSelectedImage] = useState(0)
  const [selectedSize, setSelectedSize] = useState('')

  // Available sizes for all products
  const availableSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL']

  useEffect(() => {
    setLoading(true)
    setError(null)
    
    fetch(`http://localhost/hotel-ks/backend/get_product.php?id=${id}`)
      .then(res => {
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`)
        }
        return res.json()
      })
      .then(data => {
        if (data.success) {
          setProduct(data.product)
          // Set default size if product has sizes
          if (data.product.has_sizes && data.product.sizes && data.product.sizes.length > 0) {
            const firstAvailableSize = data.product.sizes.find(s => s.stock > 0)
            if (firstAvailableSize) {
              setSelectedSize(firstAvailableSize.size)
            }
          }
        } else {
          throw new Error(data.message || 'Failed to fetch product')
        }
        setLoading(false)
      })
      .catch(err => {
        console.error('Gabim në ngarkimin e produktit:', err)
        setError(err.message)
        setLoading(false)
      })
  }, [id])

  // Get available stock based on selected size
  const availableStock = product?.has_sizes && selectedSize
    ? product.sizes.find(s => s.size === selectedSize)?.stock || 0
    : product?.stock || 0

  const handleAddToCart = () => {
    if (!product) return
    
    // Check if product has sizes and none is selected
    if (product.has_sizes && !selectedSize) {
      alert('Ju lutem zgjidhni një madhësi')
      return
    }

    console.log('Adding to cart with size:', selectedSize) // Debug log
    
    // Pass the size to addToCart - it will be null if product doesn't have sizes
    const sizeToPass = product.has_sizes ? selectedSize : null
    addToCart({ ...product, quantity }, sizeToPass)
    
    // Show toast notification
    const toast = document.createElement('div')
    toast.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-slide-in'
    toast.innerHTML = `
      <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span>${product.name}${sizeToPass ? ` (${sizeToPass})` : ''} u shtua në shportë!</span>
      </div>
    `
    document.body.appendChild(toast)
    setTimeout(() => toast.remove(), 3000)
  }

  const handleBuyNow = () => {
    if (!product) return
    
    if (product.has_sizes && !selectedSize) {
      alert('Ju lutem zgjidhni një madhësi')
      return
    }

    const sizeToPass = product.has_sizes ? selectedSize : null
    addToCart({ ...product, quantity }, sizeToPass)
    navigate('/cart')
  }

  const nextImage = () => {
    if (product && product.images && product.images.length > 1) {
      setSelectedImage((prev) => (prev + 1) % product.images.length)
    }
  }

  const prevImage = () => {
    if (product && product.images && product.images.length > 1) {
      setSelectedImage((prev) => (prev - 1 + product.images.length) % product.images.length)
    }
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-xl text-gray-600">Duke ngarkuar detajet e produktit...</p>
        </div>
      </div>
    )
  }

  if (error || !product) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center p-8">
          <div className="text-red-600 mb-4">
            <svg className="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p className="text-lg font-semibold">Gabim në ngarkimin e produktit</p>
            <p className="text-sm text-gray-600 mt-2">{error || 'Produkti nuk u gjet.'}</p>
          </div>
          <div className="flex gap-4 justify-center">
            <Link
              to="/products"
              className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold"
            >
              Kthehu te Produktet
            </Link>
            <button
              onClick={() => window.location.reload()}
              className="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-semibold"
            >
              Provo Përsëri
            </button>
          </div>
        </div>
      </div>
    )
  }

  const currentImage = product.images && product.images.length > 0 
    ? product.images[selectedImage] 
    : (product.image || 'https://via.placeholder.com/800x600?text=No+Image')

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
        <div className="bg-white rounded-lg shadow-lg overflow-hidden">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 p-8">
            {/* Images */}
            <div>
              <div className="mb-4 rounded-lg overflow-hidden bg-gray-100 relative group">
                <img
                  src={currentImage}
                  alt={product.name}
                  className="w-full h-96 object-cover"
                  onError={(e) => {
                    e.target.src = 'https://via.placeholder.com/800x600?text=No+Image'
                  }}
                />
                
                {/* Navigation Arrows - Only show if multiple images */}
                {product.images && product.images.length > 1 && (
                  <>
                    <button
                      onClick={prevImage}
                      className="absolute left-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-gray-800 p-2 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                      aria-label="Previous image"
                    >
                      <ChevronLeft className="w-6 h-6" />
                    </button>
                    <button
                      onClick={nextImage}
                      className="absolute right-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-gray-800 p-2 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                      aria-label="Next image"
                    >
                      <ChevronRight className="w-6 h-6" />
                    </button>
                    
                    {/* Image Counter */}
                    <div className="absolute bottom-4 right-4 bg-black/70 text-white px-3 py-1 rounded-full text-sm">
                      {selectedImage + 1} / {product.images.length}
                    </div>
                  </>
                )}
              </div>
              
              {/* Thumbnail Images */}
              {product.images && product.images.length > 1 && (
                <div className="grid grid-cols-5 gap-4">
                  {product.images.map((image, index) => (
                    <button
                      key={index}
                      onClick={() => setSelectedImage(index)}
                      className={`rounded-lg overflow-hidden border-2 transition-all ${
                        selectedImage === index ? 'border-blue-600 ring-2 ring-blue-200' : 'border-gray-200 hover:border-gray-300'
                      }`}
                    >
                      <img 
                        src={image} 
                        alt={`${product.name} ${index + 1}`} 
                        className="w-full h-20 object-cover"
                        onError={(e) => {
                          e.target.src = 'https://via.placeholder.com/100x100?text=No+Image'
                        }}
                      />
                    </button>
                  ))}
                </div>
              )}
            </div>

            {/* Product Info */}
            <div>
              <div className="mb-4">
                <span className="inline-block px-3 py-1 bg-blue-100 text-blue-600 text-sm font-semibold rounded-full uppercase">
                  {product.category || 'Produkt'}
                </span>
              </div>
              
              <h1 className="text-4xl font-bold text-gray-900 mb-6">{product.name}</h1>
              
              <div className="mb-6">
                <span className="text-4xl font-bold text-blue-600">€{parseFloat(product.price).toFixed(2)}</span>
                {availableStock > 0 ? (
                  <span className="ml-4 text-green-600 font-semibold">
                    <i className="fas fa-box mr-1"></i>
                    Në Magazinë ({availableStock} copë)
                  </span>
                ) : (
                  <span className="ml-4 text-red-600 font-semibold">
                    <i className="fas fa-times-circle mr-1"></i>
                    Nuk ka në magazinë
                  </span>
                )}
              </div>

              <p className="text-gray-700 text-lg mb-6 leading-relaxed">{product.description}</p>

              {/* Size Selection */}
              <div className="mb-6">
                <label className="block text-sm font-semibold text-gray-900 mb-3">
                  Zgjidhni Madhësinë <span className="text-red-500">*</span>
                </label>
                <div className="flex flex-wrap gap-3">
                  {availableSizes.map((size) => {
                    const isSelected = selectedSize === size
                    
                    return (
                      <button
                        key={size}
                        onClick={() => setSelectedSize(size)}
                        className={`
                          min-w-[60px] px-4 py-3 border-2 rounded-lg font-semibold transition-all
                          ${isSelected
                            ? 'bg-blue-600 text-white border-blue-600 shadow-md scale-105'
                            : 'bg-white text-gray-700 border-gray-300 hover:border-blue-600 hover:shadow-sm'
                          }
                        `}
                      >
                        <div className="text-center">
                          <div className="text-lg">{size}</div>
                        </div>
                      </button>
                    )
                  })}
                </div>
                {!selectedSize && (
                  <p className="text-sm text-red-600 mt-2">
                    <i className="fas fa-exclamation-circle mr-1"></i>
                    Ju lutem zgjidhni një madhësi
                  </p>
                )}
              </div>

              {/* Quantity Selector */}
              <div className="mb-6">
                <label className="block text-sm font-semibold text-gray-900 mb-2">Sasia</label>
                <div className="flex items-center space-x-4">
                  <button
                    onClick={() => setQuantity(Math.max(1, quantity - 1))}
                    className="w-10 h-10 rounded-lg border-2 border-gray-300 hover:border-blue-600 flex items-center justify-center font-semibold transition-colors"
                  >
                    -
                  </button>
                  <span className="text-xl font-semibold w-12 text-center">{quantity}</span>
                  <button
                    onClick={() => setQuantity(Math.min(availableStock, quantity + 1))}
                    className="w-10 h-10 rounded-lg border-2 border-gray-300 hover:border-blue-600 flex items-center justify-center font-semibold transition-colors"
                    disabled={quantity >= availableStock}
                  >
                    +
                  </button>
                </div>
                {availableStock > 0 && (
                  <p className="text-sm text-gray-600 mt-2">
                    Njësive në magazinë: {availableStock}
                  </p>
                )}
              </div>

              {/* Action Buttons */}
              <div className="flex gap-4 mb-8">
                <button
                  onClick={handleAddToCart}
                  disabled={availableStock === 0}
                  className="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 disabled:bg-gray-400 disabled:cursor-not-allowed"
                >
                  <i className="fas fa-shopping-cart mr-2"></i>
                  Shto në Shportë
                </button>
                <button
                  onClick={handleBuyNow}
                  disabled={availableStock === 0}
                    className="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 disabled:bg-gray-400 disabled:cursor-not-allowed"
                >
                  <i className="fas fa-bolt mr-2"></i>
                  Bli Tani
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default ProductDetail
