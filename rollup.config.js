import { babel } from "@rollup/plugin-babel";
import { terser } from "rollup-plugin-terser";
import cleanup from "rollup-plugin-cleanup";

const paths = {
  src: "./js",
  dest: "./dist/js",
  files: [],
};

const defaultTerserOptions = {
  mangle: {
    reserved: ["Drupal"],
  },
};

export default args => {
  const sourcemap = args.sourcemap || false;

  return [
    ...paths.files.map(file => {
      return {
        input: `${paths.src}/${file}.js`,
        plugins: [babel({ babelHelpers: "inline" }), cleanup()],
        output: [
          {
            name: file,
            format: "cjs",
            sourcemap,
            file: `${paths.dest}/${file}.js`,
          },
          {
            name: file,
            format: "cjs",
            sourcemap,
            file: `${paths.dest}/${file}.min.js`,
            plugins: [terser(defaultTerserOptions)],
          },
        ],
      };
    }),
  ];
};
