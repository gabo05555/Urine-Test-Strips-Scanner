# Colometric Analysis Web

Colometric Analysis Web is a web application designed to perform colorimetric analysis on images. It allows users to upload images, analyze color data, and visualize results interactively.

## Features

- Upload and process images for color analysis
- Visualize color distributions and statistics
- Interactive user interface
- Export analysis results

## Getting Started

### Prerequisites

- Python 3.8+ (for Flask backend)
- Node.js 
- pip (Python package manager)
- npm or yarn (for frontend)
- XAMPP (for MySQL database)

### Installation

1. Clone the repository:
   ```sh
   git clone https://github.com/yourusername/Colometric-Analysis-Web.git
   cd Colometric-Analysis-Web
   ```

2. **Set up the database using XAMPP:**
   - Start Apache and MySQL from the XAMPP control panel.
   - Open phpMyAdmin (usually at [http://localhost/phpmyadmin](http://localhost/phpmyadmin)).
   - Create a new database named `u467106394_colometric`.
   - Import the provided SQL file: `u467106394_colometric.sql` (located in the project directory).

3. Install backend dependencies:
   ```sh
   pip install -r requirements.txt
   ```

4. Install frontend dependencies:
   ```sh
   cd frontend
   npm install
   # or
   yarn install
   cd ..
   ```

### Running the Application

Start the Flask backend server:
```sh
flask run
```

Start the frontend development server (in a separate terminal):
```sh
cd frontend
npm start
# or
yarn start
```
The frontend app will be available at `http://localhost:3000` and will communicate with the Flask backend.

## Usage

1. Open the application in your browser.
2. Upload an image using the provided interface.
3. View and interact with the color analysis results.

## Contributing

Contributions are welcome! Please open issues or submit pull requests for improvements.

## License

This project is licensed under the MIT License.
