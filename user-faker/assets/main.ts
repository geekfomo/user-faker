type User = {
  id:number,
  login:string
}
type UserData = {
  code: number,
  users: User[],
  realId: number,
  fakeId: number
}


class UserFaker {
  constructor() {
    this.elemHead = (document.getElementsByClassName(this.idHead)[0])?.children[0] as HTMLElement;
    this.elemBody = document.getElementsByClassName(this.idBody)[0] as HTMLElement;
    console.log(this.elemHead, this.elemBody);
    if ( this.elemBody && this.elemHead) {
      fetch(this.endpoint).then((res: any)  => {
        res.json().then((data: UserData) => {
          if ( data.code !== 0 ) {
            return
          }
          const popUser = (arr: User[], id: number): User|null => {
            for (let kk = 0; kk < arr.length; kk++) {
              if (arr[kk].id === id) {
                const user = arr[kk];
                arr.splice(kk,1);
                return user;
              }
            }
            return null;
          };
          const realUser: User|null = popUser(data.users, data.realId);
          const fakeUser: User|null = popUser(data.users, data.fakeId);

          let body: string = '';
          let head: string = '';
          body += this.addUser( {id: 0 , login: "Real User"}  , this.isRealUser(data) );

          if ( realUser !== null ) {
            body += this.addUser( realUser,  this.isRealUser(data));
          }
          if ( fakeUser !== null ) {
            body += this.addUser( fakeUser,  true);
          }
          for( const user of data.users ) {
            body += this.addUser(user, user.id === data.fakeId || (data.fakeId === 0 && user.id === data.realId) );
          }
          (this.elemBody as HTMLElement).innerHTML = body;


          if ( fakeUser !== null ) {
            (this.elemHead as HTMLElement).innerHTML = `${fakeUser.login}`;
          } else if ( realUser !== null ) {
            (this.elemHead as HTMLElement).innerHTML = `Real: ${realUser.login}`;
          }
          let elems = document.getElementsByClassName('uf');
          for( let kk=0; kk < elems.length; kk++ ) {
            elems[kk].addEventListener('click', () => {
              this.clickUser(elems[kk] as HTMLElement)
            }, false);
          }
        });
      });
    }
  }

  private addUser(user: User, active: boolean) {
    return `<div class='uf ${active ? "active":""}' data-id="${user.id}" data-login="${user.login}"><span class="ufId">${user.id}</span><span class="ufLogin">${user.login}</span></div>`;
  }

  private isRealUser(data: UserData): boolean {
    return data.fakeId === data.realId || data.fakeId === 0;
  }

  private clickUser(elem: HTMLElement) {
    if ( !elem.classList.contains('active') ) {
      elem.classList.add('active');
      const id = elem.dataset.id;
      let elems = document.getElementsByClassName('uf');
      for (let kk: number = 0; kk < elems.length; kk++) {
        const elem1: HTMLElement = elems[kk] as HTMLElement;
        if (elem1.dataset.id !== id) {
          elem1.classList.remove('active');
        }
      }
      this.updateCookie(parseInt(elem.dataset.id ?? '') );
      window.location.reload();
    }
  }

  private updateCookie(id: number|null) {
    const keyFake = '$this->keyFake';
    if ( id !== -1 && id !== null ) {
      document.cookie = `${this.keyFake}=${id}; path=/ ; Secure`;
    } else {
      document.cookie = `${this.keyFake}=; path=/ ; Secure ; Expires=Thu, 01 Jan 1970 00:00:01 GMT;`
    }
  }
  private readonly endpoint : string = '/wp-json/user-faker/users';
  private readonly elemHead: HTMLElement | null;
  private readonly elemBody: HTMLElement | null;
  private readonly idHead:string = 'fakeUserHead';
  private readonly idBody:string = 'fakeUserBody';
  private readonly keyFake:string = 'wordpress_userFakeId'
}

window.addEventListener( 'DOMContentLoaded', () => {
  const uf: UserFaker = new UserFaker();
})
