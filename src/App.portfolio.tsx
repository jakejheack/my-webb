import React from 'react';

const App: React.FC = () => {
  return (
    <div style={{ 
      padding: '40px', 
      textAlign: 'center', 
      fontFamily: 'Arial, sans-serif',
      background: '#0F0F23',
      color: '#ffffff',
      minHeight: '100vh'
    }}>
      <h1 style={{ color: '#00D9FF', marginBottom: '20px' }}>Jake Portfolio</h1>
      <p style={{ fontSize: '18px', marginBottom: '30px' }}>
        This portfolio has been converted to pure PHP.
      </p>
      <p style={{ color: '#A0A0A0', marginBottom: '20px' }}>
        Please access the portfolio directly via the PHP files:
      </p>
      <div style={{ background: 'rgba(255,255,255,0.1)', padding: '20px', borderRadius: '10px', margin: '20px 0' }}>
        <strong>Main Portfolio:</strong> index.php<br/>
        <strong>Admin Panel:</strong> backend/admin/login.php
      </div>
      <p style={{ color: '#7C3AED' }}>
        The React version is no longer active. All functionality is now in PHP.
      </p>
    </div>
  );
};

export default App;