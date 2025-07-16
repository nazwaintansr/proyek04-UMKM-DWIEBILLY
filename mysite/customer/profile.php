<?php
session_start();
require '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'customer'");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("Pengguna tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;

    $stmt = $pdo->prepare("UPDATE users SET phone = ?, address = ?, latitude = ?, longitude = ? WHERE id = ?");
    $stmt->execute([$phone, $address, $latitude, $longitude, $user_id]);

    $message = "Profil berhasil diperbarui!";

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
}

$defaultLat = $user['latitude'] ?: -7.797068;
$defaultLng = $user['longitude'] ?: 110.370529;
?>

<!DOCTYPE html>
<html>
<head>
  <title>Profil Saya</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://unpkg.com/maplibre-gl@2.4.0/dist/maplibre-gl.css" rel="stylesheet" />
  

  <style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap');
  </style>
  
<style>
  :root {
    --pink-light: #fef3f3;
    --pink-dark: #ffd6d6;
    --green-light: #e7fdf0;
    --green-dark: #b6e5c9;
    --blue-light: #e6f4fd;
    --blue-dark: #add6f5;
    --yellow-light: #fffde1;
    --yellow-dark: #fff5a5;
    --white: #fffdfc;
    --text-dark: #212529;
    --text-muted: #555;
  }

  body {
    font-family: 'Nunito', sans-serif;
    background: linear-gradient(135deg, var(--pink-light), var(--blue-light), var(--yellow-light), var(--green-light));
    background-size: 400% 400%;
    animation: gradientBG 10s ease infinite;
    padding: 2rem 1rem;
    margin: 0;
  }

  @keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }

  h2 {
    text-align: center;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 2rem;
    letter-spacing: 0.05em;
  }

  form {
    max-width: 480px;
    margin: 0 auto;
    background-color: var(--yellow-light);
    padding: 2rem;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    transition: box-shadow 0.3s ease;
  }

  form:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
  }

  label {
    display: block;
    font-weight: 600;
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
    color: var(--text-dark);
    letter-spacing: 0.02em;
  }

  input[type="text"],
  textarea {
    width: 100%;
    padding: 0.6rem 1rem;
    font-size: 1rem;
    border-radius: 12px;
    border: 1.8px solid var(--pink-dark);
    background-color: var(--white);
    color: var(--text-dark);
    resize: vertical;
    box-sizing: border-box;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    font-weight: 500;
    font-family: 'Nunito', sans-serif;
  }

  input[type="text"]:disabled {
    background-color: var(--white);
    color: var(--text-muted);
    cursor: not-allowed;
  }

  input[type="text"]:focus,
  textarea:focus {
    outline: none;
    border-color: var(--pink-dark);
    box-shadow: 0 0 8px var(--pink-dark);
    background-color: var(--white);
  }

  button,
  #locate-btn {
    width: 100%;
    padding: 0.5rem 1rem;
    margin-top: 1.5rem;
    border-radius: 50px;
    font-weight: 550;
    font-size: 1rem;
    cursor: pointer;
    font-family: 'Nunito', sans-serif;
    color: var(--text-dark);
    background-color: var(--green-light);
    border: 1.5px solid var(--green-dark);
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
  }

  button:hover,
  #locate-btn:hover {
    background-color: var(--green-dark);
    border-color: var(--green-dark);
    color: var(--text-dark);
  }

  .success {
    color: var(--green-dark);
    font-weight: 550;
    text-align: center;
    margin-bottom: 1rem;
    font-size: 1rem;
    letter-spacing: 0.02em;
    font-family: 'Nunito', sans-serif;
  }

  #map {
    height: 300px;
    border-radius: 12px;
    border: 1.5px solid var(--pink-dark);
    margin-top: 1rem;
    box-shadow: inset 0 0 10px rgba(255, 214, 79, 0.2);
    transition: box-shadow 0.3s ease;
  }

  #map:hover {
    box-shadow: inset 0 0 15px rgba(255, 214, 79, 0.4);
  }

.back-button {
  width: 50%;
  padding: 0.5rem 1rem;
  margin-top: 1.5rem;
  border-radius: 50px;
  font-weight: 550;
  font-size: 1rem;
  cursor: pointer;
  font-family: 'Nunito', sans-serif;
  color: var(--text-dark);
  background-color: var(--green-light);
  border: 1.5px solid var(--green-dark);
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.back-button:hover {
  background-color: var(--green-dark);
  border-color: var(--green-dark);
  color: var(--text-dark);
}


  .half-width {
    flex: 1;
  }

  @media (max-width: 520px) {
    form {
      padding: 1.5rem;
    }
  }
</style>


  </style>
</head>
<body>

  <h2>PROFILE</h2>

  <?php if (!empty($message)): ?>
    <p class="success"><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>
  <form method="post">
    <label for="username">Username</label>
    <input type="text" id="username" value="<?= htmlspecialchars($user['username']) ?>" disabled>

    <label for="phone">Nomor HP</label>
    <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>

    <label for="address">Alamat</label>
    <textarea name="address" id="address" rows="3" required><?= htmlspecialchars($user['address']) ?></textarea>

    <label>Lokasi (klik atau gunakan tombol lokasi otomatis)</label>
    <div id="map"></div>

    <input type="hidden" name="latitude" id="latitude" value="<?= htmlspecialchars($user['latitude']) ?>">
    <input type="hidden" name="longitude" id="longitude" value="<?= htmlspecialchars($user['longitude']) ?>">

    <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
   <button type="button" id="locate-btn" class="half-width">Lokasi Otomatis</button>
    <button type="button" onclick="submitAndRedirect()" class="back-button">Simpan & Kembali</button>

  </form>

<script src="https://unpkg.com/maplibre-gl@2.4.0/dist/maplibre-gl.js"></script>
<script>

const defaultLat = <?= json_encode(floatval($defaultLat)) ?>;
const defaultLng = <?= json_encode(floatval($defaultLng)) ?>;
const latInput = document.getElementById('latitude');
const lngInput = document.getElementById('longitude');
const addressInput = document.getElementById('address');

const map = new maplibregl.Map({
  container: 'map',
  style: 'https://demotiles.maplibre.org/style.json',
  center: [defaultLng, defaultLat],
  zoom: 13
});

const marker = new maplibregl.Marker({ draggable: true })
  .setLngLat([defaultLng, defaultLat])
  .addTo(map);

function updateLatLng(lngLat) {
  latInput.value = lngLat.lat.toFixed(8);
  lngInput.value = lngLat.lng.toFixed(8);
  fetchAddress(lngLat.lat, lngLat.lng);
}

function fetchAddress(lat, lng) {
  fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
    .then(response => response.json())
    .then(data => {
      if (data && data.display_name) {
        addressInput.value = data.display_name;
      }
    })
    .catch(() => {
      console.log("Gagal mengambil alamat.");
    });
}

marker.on('dragend', () => {
  const lngLat = marker.getLngLat();
  updateLatLng(lngLat);
});

map.on('click', (e) => {
  marker.setLngLat(e.lngLat);
  updateLatLng(e.lngLat);
});

document.getElementById('locate-btn').addEventListener('click', () => {
  if (!navigator.geolocation) {
    alert("Browser Anda tidak mendukung geolokasi.");
    return;
  }

  navigator.geolocation.getCurrentPosition((position) => {
    const lat = position.coords.latitude;
    const lng = position.coords.longitude;
    map.setCenter([lng, lat]);
    map.setZoom(15);
    marker.setLngLat([lng, lat]);
    updateLatLng({ lat, lng });
  }, (error) => {
    alert("Gagal mendapatkan lokasi: " + error.message);
  });
});
</script>

<script>
  function submitAndRedirect() {
    const form = document.querySelector('form');
    const formData = new FormData(form);

    fetch(window.location.href, {
      method: 'POST',
      body: formData
    })
    .then(response => {
      if (response.ok) {
        window.location.href = 'products.php';
      } else {
        alert('Gagal menyimpan perubahan.');
      }
    })
    .catch(() => {
      alert('Terjadi kesalahan saat menyimpan.');
    });
  }
</script>

</body>
</html>
