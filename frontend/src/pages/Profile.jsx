
import { api } from '../services/api'
import { useState, useEffect } from 'react'
import { User, Mail, Phone, MapPin, Globe, Hash } from 'lucide-react'
import { useAuth } from '../context/AuthContext'

const Profile = () => {
  const { user, login } = useAuth()
  
  const [formData, setFormData] = useState({
    unique_id: '',
    name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    country: ''
  })
  
  const [isEditing, setIsEditing] = useState(false)
  const [isLoading, setIsLoading] = useState(true)
  const [isSaving, setIsSaving] = useState(false)
  const [errors, setErrors] = useState({})
  const [successMessage, setSuccessMessage] = useState('')
  const [errorMessage, setErrorMessage] = useState('')

  // Fetch user profile on component mount
  useEffect(() => {
    fetchProfile()
  }, [])

  const fetchProfile = async () => {
    try {
      setIsLoading(true)
      const response = await api.getProfile()
      
      if (response.success && response.user) {
        setFormData({
          unique_id: response.user.unique_id || '',
          name: response.user.name || '',
          email: response.user.email || '',
          phone: response.user.phone || '',
          address: response.user.address || '',
          city: response.user.city || '',
          country: response.user.country || ''
        })
      }
    } catch (error) {
      console.error('Error fetching profile:', error)
      setErrorMessage('Nuk mund të ngarkohet profili. Ju lutem provoni përsëri.')
    } finally {
      setIsLoading(false)
    }
  }

  const handleChange = (e) => {
    const { name, value } = e.target
    setFormData(prev => ({
      ...prev,
      [name]: value
    }))
    
    // Clear error when user starts typing
    if (errors[name]) {
      setErrors(prev => ({
        ...prev,
        [name]: ''
      }))
    }
    setSuccessMessage('')
    setErrorMessage('')
  }

  const validateForm = () => {
    const newErrors = {}

    if (!formData.name.trim()) {
      newErrors.name = 'Emri është i detyrueshëm'
    }

    if (formData.phone && !/^[\d\s\-\+\(\)]+$/.test(formData.phone)) {
      newErrors.phone = 'Numri i telefonit nuk është i vlefshëm'
    }

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setSuccessMessage('')
    setErrorMessage('')

    if (!validateForm()) {
      return
    }

    setIsSaving(true)

    try {
      const response = await api.updateProfile({
        name: formData.name,
        phone: formData.phone,
        address: formData.address,
        city: formData.city,
        country: formData.country
      })

      if (response.success) {
        // Update user context with new data
        login(response.user)
        
        setSuccessMessage('Profili u përditësua me sukses!')
        setIsEditing(false)
        
        // Clear success message after 3 seconds
        setTimeout(() => {
          setSuccessMessage('')
        }, 3000)
      }
    } catch (error) {
      console.error('Error updating profile:', error)
      setErrorMessage(error.message || 'Përditësimi i profilit dështoi. Ju lutem provoni përsëri.')
    } finally {
      setIsSaving(false)
    }
  }

  const handleCancel = () => {
    // Reset form to original values
    fetchProfile()
    setIsEditing(false)
    setErrors({})
    setSuccessMessage('')
    setErrorMessage('')
  }

  if (isLoading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-700 mx-auto"></div>
          <p className="mt-4 text-gray-600">Duke ngarkuar profilin...</p>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-3xl mx-auto">
        <div className="bg-white rounded-2xl shadow-xl overflow-hidden">
          {/* Header */}
          <div className="bg-gradient-to-r from-gray-700 to-gray-900 px-8 py-12">
            <div className="flex items-center space-x-4">
              <div className="bg-white rounded-full p-4">
                <User className="w-12 h-12 text-gray-800" />
              </div>
              <div className="text-white">
                <h1 className="text-3xl font-bold">{formData.name || 'Përdoruesi'}</h1>
                <p className="text-blue-100 mt-1">{formData.email}</p>
                {formData.unique_id && (
                  <div className="mt-2 inline-flex items-center bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full">
                    <Hash className="w-4 h-4 mr-1" />
                    <span className="text-sm font-mono font-semibold">ID: {formData.unique_id}</span>
                  </div>
                )}
              </div>
            </div>
          </div>

          {/* Messages */}
          <div className="px-8 pt-6">
            {successMessage && (
              <div className="mb-4 p-4 bg-green-50 border border-green-200 text-green-600 rounded-lg">
                {successMessage}
              </div>
            )}

            {errorMessage && (
              <div className="mb-4 p-4 bg-red-50 border border-red-200 text-red-600 rounded-lg">
                {errorMessage}
              </div>
            )}
          </div>

          {/* Form */}
          <form onSubmit={handleSubmit} className="px-8 py-6">
            <div className="space-y-6">
              {/* Unique ID (Read-only) */}
              <div>
                <label htmlFor="unique_id" className="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <Hash className="w-4 h-4 mr-2" />
                  ID Unik
                </label>
                <div className="relative">
                  <input
                    id="unique_id"
                    name="unique_id"
                    type="text"
                    value={formData.unique_id}
                    disabled
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed font-mono text-lg font-semibold text-gray-800"
                  />
                  <div className="absolute right-3 top-1/2 transform -translate-y-1/2">
                    <span className="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-blue-800">
                      Vetëm për lexim
                    </span>
                  </div>
                </div>
                <p className="mt-1 text-xs text-gray-500">
                  <i className="fas fa-info-circle mr-1"></i>
                  Ky është ID-ja juaj unike që nuk mund të ndryshohet
                </p>
              </div>

              {/* Name */}
              <div>
                <label htmlFor="name" className="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <User className="w-4 h-4 mr-2" />
                  Emri i Plotë
                </label>
                <input
                  id="name"
                  name="name"
                  type="text"
                  value={formData.name}
                  onChange={handleChange}
                  disabled={!isEditing}
                  className={`w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all ${
                    errors.name ? 'border-red-500' : 'border-gray-300'
                  } ${!isEditing ? 'bg-gray-50 cursor-not-allowed' : ''}`}
                  placeholder="Shkruani emrin tuaj"
                />
                {errors.name && (
                  <p className="mt-1 text-sm text-red-600">{errors.name}</p>
                )}
              </div>

              {/* Email (read-only) */}
              <div>
                <label htmlFor="email" className="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <Mail className="w-4 h-4 mr-2" />
                  Email
                </label>
                <input
                  id="email"
                  name="email"
                  type="email"
                  value={formData.email}
                  disabled
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
                />
              </div>

              {/* Phone */}
              <div>
                <label htmlFor="phone" className="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <Phone className="w-4 h-4 mr-2" />
                  Numri i Telefonit
                </label>
                <input
                  id="phone"
                  name="phone"
                  type="tel"
                  value={formData.phone}
                  onChange={handleChange}
                  disabled={!isEditing}
                  className={`w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all ${
                    errors.phone ? 'border-red-500' : 'border-gray-300'
                  } ${!isEditing ? 'bg-gray-50 cursor-not-allowed' : ''}`}
                  placeholder="+355 69 123 4567"
                />
                {errors.phone && (
                  <p className="mt-1 text-sm text-red-600">{errors.phone}</p>
                )}
              </div>

              {/* Address */}
              <div>
                <label htmlFor="address" className="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <MapPin className="w-4 h-4 mr-2" />
                  Adresa
                </label>
                <input
                  id="address"
                  name="address"
                  type="text"
                  value={formData.address}
                  onChange={handleChange}
                  disabled={!isEditing}
                  className={`w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all ${
                    !isEditing ? 'bg-gray-50 cursor-not-allowed' : ''
                  }`}
                  placeholder="Rruga e Durrësit"
                />
              </div>

              {/* City */}
              <div>
                <label htmlFor="city" className="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <Globe className="w-4 h-4 mr-2" />
                  Qyteti
                </label>
                <input
                  id="city"
                  name="city"
                  type="text"
                  value={formData.city}
                  onChange={handleChange}
                  disabled={!isEditing}
                  className={`w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all ${
                    !isEditing ? 'bg-gray-50 cursor-not-allowed' : ''
                  }`}
                  placeholder="Tiranë"
                />
              </div>

              {/* Country */}
              <div>
                <label htmlFor="country" className="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <Globe className="w-4 h-4 mr-2" />
                  Shteti
                </label>
                <input
                  id="country"
                  name="country"
                  type="text"
                  value={formData.country}
                  onChange={handleChange}
                  disabled={!isEditing}
                  className={`w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all ${
                    !isEditing ? 'bg-gray-50 cursor-not-allowed' : ''
                  }`}
                  placeholder="Shqipëri"
                />
              </div>
            </div>

            {/* Buttons */}
            <div className="flex justify-end space-x-4 pt-6">
              {isEditing ? (
                <>
                  <button
                    type="button"
                    onClick={handleCancel}
                    className="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                  >
                    Anulo
                  </button>
                  <button
                    type="submit"
                    disabled={isSaving}
                    className="px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {isSaving ? 'Duke ruajtur...' : 'Ruaj Ndryshimet'}
                  </button>
                </>
              ) : (
                <button
                  type="button"
                  onClick={() => setIsEditing(true)}
                  className="px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors"
                >
                  Ndrysho Profilin
                </button>
              )}
            </div>
          </form>
        </div>
      </div>
    </div>
  )
}

export default Profile
