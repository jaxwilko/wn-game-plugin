import Random from "../utils/random";

export default class Sprites {
    constructor() {
        this.random = new Random();

        this.imageMap = {};
        this.spriteMap = {};
    }

    getSprite(object) {
        if (!this.spriteMap[object.id]) {
            this.spriteMap[object.id] = {};
        }

        if (!object.spriteMap[object.animation]) {
            throw new Error(`Animation '${object.animation}' not found in: ${JSON.stringify(object)}`);
        }

        let objectSpriteMap = null;

        if (!this.spriteMap[object.id][object.animation]) {
            this.spriteMap[object.id][object.animation] = {
                tick: 0,
                frame: 0,
                imageWidth: 0,
                imageHeight: 0,
                columns: 0,
                config: object.spriteMap[object.animation],
                image: this.makeImage(object.spriteMap[object.animation].sheet, () => {
                    objectSpriteMap.imageWidth = objectSpriteMap.image.width;
                    objectSpriteMap.imageHeight = objectSpriteMap.image.height;
                    objectSpriteMap.columns = objectSpriteMap.imageWidth / this.getAlign(objectSpriteMap.config.align, 'x');
                }),
            };
            // hack for referencing object shortcut
            objectSpriteMap = this.spriteMap[object.id][object.animation];
        }

        if (!objectSpriteMap) {
            objectSpriteMap = this.spriteMap[object.id][object.animation];
        }

        objectSpriteMap.tick++;

        // handle random animation delays
        if (object.animationRandomDelay && this.random.chance() && objectSpriteMap.tick >= objectSpriteMap.config.delay) {
            objectSpriteMap.tick -= this.random.number(5, 15);
        }

        if (objectSpriteMap.tick >= objectSpriteMap.config.delay) {
            objectSpriteMap.tick = 0;
            objectSpriteMap.frame++;

            if (objectSpriteMap.frame >= objectSpriteMap.columns) {
                objectSpriteMap.frame = 0;
            }
        }

        return {
            image: objectSpriteMap.image,
            imageX: objectSpriteMap.frame * this.getAlign(objectSpriteMap.config.align, 'x'),
            imageY: 0,
            sizeX: this.getAlign(objectSpriteMap.config.align, 'x'),
            sizeY: this.getAlign(objectSpriteMap.config.align, 'y'),
        };
    }

    getAlign(align, symbol) {
        return Array.isArray(align) ? align[symbol === 'x' ? 0 : 1] : align;
    }

    drawSprite(x, y, object, ctx) {
        const sprite = this.getSprite(object);
        ctx.drawImage(
            sprite.image,
            sprite.imageX,
            sprite.imageY,
            sprite.sizeX,
            sprite.sizeY,
            x,
            y,
            object.size.x,
            object.size.y
        );
    }

    makeImage(src, callback) {
        const image = new Image();
        image.onload = callback;
        image.src = src;
        return image;
    }

    getImage(src) {
        return this.imageMap[src] || this.makeImage(src);
    }
}
