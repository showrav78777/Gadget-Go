import './index.css';
function App() {
    return (
      <div className="container mx-auto p-6">
        <h1 className="text-3xl font-bold text-blue-600">E-commerce Store</h1>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="bg-gray-100 p-4 rounded-lg shadow">
            <h2 className="text-xl">Product 1</h2>
            <p>$99.99</p>
          </div>
        </div>
      </div>
    );
  }
  export default App;