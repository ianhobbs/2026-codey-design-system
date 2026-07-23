const fs = require('fs');

// Input CSS file
const inputFilePath = './src/assets/css/lib/utopia-pre.css';
// Output CSS file
const outputFilePath = './src/assets/css/lib/utopia-export.css';

// Mapping of old variables to new variables
const variableMapping = {
  '--text--2': '--text-xs',
  '--text--1': '--text-sm',
  '--text-0': '--text-base',
  '--text-1': '--text-lg',
  '--text-2': '--text-xl',
  '--text-3': '--text-2xl',
  '--text-4': '--text-3xl',
  '--text-5': '--text-4xl',
  '--text-6': '--text-5xl',
  '--text-7': '--text-6xl',
  '--text-8': '--text-7xl',
  '--text-9': '--text-8xl',
  '--spacing-3xs': '--spacing-1',
  '--spacing-2xs': '--spacing-2',
  '--spacing-xs': '--spacing-3',
  '--spacing-s': '--spacing-4',
  '--spacing-m': '--spacing-5',
  '--spacing-l': '--spacing-6', 
  '--spacing-xl': '--spacing-7',
  '--spacing-2xl': '--spacing-8', 
  '--spacing-3xl': '--spacing-9', 
  '--spacing-4xl': '--spacing-10',
  '--spacing-5xl': '--spacing-11',
  '--spacing-6xl': '--spacing-12',
  '--spacing-7xl': '--spacing-13',
  '--spacing-8xl': '--spacing-14',
  '--spacing-9xl': '--spacing-15',
  '--spacing-10xl': '--spacing-16',
  '--spacing-11xl': '--spacing-17',
  '--spacing-3xs-2xs': '--spacing-3xs-2xs',
};

// Read the input CSS file
fs.readFile(inputFilePath, 'utf8', (err, data) => {
  if (err) {
    console.error('Error reading the input file:', err);
    return;
  }

  // Replace old variables with new variables
  let convertedCSS = data;
  for (const [oldVar, newVar] of Object.entries(variableMapping)) {
    const regex = new RegExp(oldVar, 'g');
    convertedCSS = convertedCSS.replace(regex, newVar);
  }

  // Write the converted CSS to the output file
  fs.writeFile(outputFilePath, convertedCSS, 'utf8', (err) => {
    if (err) {
      console.error('Error writing the output file:', err);
      return;
    }
    console.log('CSS variables converted and saved to', outputFilePath);
  });
});