import fs from 'fs';
import path from 'path';
import url from 'url';
import RequestShortener from 'webpack/lib/RequestShortener';

let previousChunks = {};

function ExtractAssets(modules, requestShortener, publicPath) {
  var emitted = false;
  var assets = modules
    .map(m => {
      var assets = Object.keys(m.assets || {});

      if (assets.length === 0) {
        return undefined;
      }

      var asset = assets[0];
      emitted = emitted || m.assets[asset].emitted;

      return {
        name: m.readableIdentifier(requestShortener),
        asset: asset
      };
    }).filter(m => {
      return m !== undefined;
    }).reduce((acc, m) => {
        acc[m.name] = url.resolve(publicPath, m.asset);
      return acc;
    }, {});

  return [emitted, assets];
}

function ExtractChunks(chunks, publicPath) {
  var mappedChunks = chunks
    .sort((a, b) => {
      if (a.hasRuntime() !== b.hasRuntime()) {
        return b.hasRuntime() ? 1 : -1;
      } else {
        return b.id - a.id;
      }
    })
    .reduce((acc, c) => {
      acc[c.name] = c.files
        .filter(f => path.extname(f).slice(0, 4) !== '.map')
        .map(f => url.resolve(publicPath, f));
      return acc;
    }, {});

  const emitted = JSON.stringify(previousChunks) !== JSON.stringify(mappedChunks);
  previousChunks = mappedChunks;

  return [emitted, mappedChunks];
}

export default class AssetMapPlugin {
  /**
   * AssetMapPlugin
   *
   * @param {string} outputFile - Where to write the asset map file
   * @param {string} [relativeTo] - Key assets relative to this path, otherwise defaults to be relative to the directory where the outputFile is written
   */
  constructor(outputFile, relativeTo) {
    this.outputFile = outputFile;
    this.relativeTo = relativeTo;
  }

  apply(compiler) {
    compiler.plugin('done', ({ compilation }) => {
      var publicPath = compilation.outputOptions.publicPath;
      var requestShortener = new RequestShortener(this.relativeTo || path.dirname(this.outputFile));

      var [assetsEmitted, assets] = ExtractAssets(compilation.modules, requestShortener, publicPath);
      var [chunksEmitted, chunks] = ExtractChunks(compilation.chunks, publicPath);

      if (assetsEmitted || chunksEmitted) {
        fs.writeFileSync(this.outputFile, JSON.stringify({ assets, chunks }, null, 2));
      }
    });
  }
}
